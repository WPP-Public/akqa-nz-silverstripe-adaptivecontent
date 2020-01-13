<?php

use Heyday\VersionedDataObjects\VersionedDataObject;

/**
 * Class AdaptiveContentHierarchy
 */
class AdaptiveContentHierarchy extends DataExtension
{
    /**
     * @var bool
     */
    protected $skipChildrenField = false;

    private static $db = array(
        'HierarchyTitle'      => 'Varchar(255)',
        'HierarchyIdentifier' => 'Varchar(255)',
        'HierarchyDepth'      => 'Int',
        'Sort'                => 'Int'
    );

    private static $default_sort = 'Sort ASC';

    private static $indexes = array(
        'HierarchyIdentifier' => true
    );

    /**
     * @param $class
     * @param $extension
     * @param $args
     * @return array
     */
    public static function get_extra_config($class, $extension, $args)
    {
        return array(
            'has_one'  => array(
                'Parent' => $class
            ),
            'has_many' => array(
                'Children' => $class
            )
        );
    }

    /**
     * @param $fields
     */
    public function updateSummaryFields(&$fields)
    {
        $fields = array_merge(
            $fields,
            array(
                'HierarchyTitle' => 'Title'
            )
        );
    }

    public function onBeforeWrite()
    {
        $parent = $this->owner;

        $titles = array(
            $parent->Title
        );

        $identifiers = array(
            $parent->Identifier
        );

        while ($parent->ParentID) {
            $parent = $parent->Parent();
            $titles[] = $parent->Title;
            $identifiers[] = $parent->Identifier;
        }

        $this->owner->HierarchyTitle = implode(' -> ', array_reverse($titles));
        $this->owner->HierarchyIdentifier = implode('/', array_reverse($identifiers));
        $this->owner->HierarchyDepth = count($identifiers) - 1;

        $children = $this->owner->Children();

        if ($children instanceof SS_List && $children->count()) {
            foreach ($children as $child) {
                $child->write();
            }
        }
    }

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('HierarchyTitle');
        $fields->removeByName('Parent');

        $fields->addFieldToTab(
            'Root.Children',
            new GridField(
                'Children',
                'Children',
                $this->owner->Children(),
                $config = GridFieldConfig_RecordEditor::create()
            )
        );

        $where = sprintf(
            "ParentID = '%s'",
            $this->owner->ID
        );

        if ($this->owner->hasExtension('VersionedDataObject')) {
            $config->addComponent(new GridFieldWhereableVersionedOrderableRows('Sort', $where));
            $config->removeComponentsByType('GridFieldDetailForm');
            $config->addComponent(new VersionedDataObjectDetailsForm());
        } else {
            $config->addComponent(new GridFieldWhereableOrderableRows('Sort', $where));
        }
    }

    /**
     * @return DataList
     */
    protected function getSiblings()
    {
        return DataList::create($this->owner->ClassName)
            ->filter('ParentID', $this->owner->ParentID)
            ->sort('Sort', 'ASC');
    }
}
