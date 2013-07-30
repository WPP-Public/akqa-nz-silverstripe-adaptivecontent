<?php

/**
 * Class AdaptiveContentHierarchy
 */
class AdaptiveContentHierarchy extends DataExtension
{
    /**
     * @var bool
     */
    protected $skipChildrenField = false;
    /**
     * @var array
     */
    private static $db = array(
        'HierarchySortOrder'  => 'Varchar(255)',
        'HierarchyTitle'      => 'Varchar(255)',
        'HierarchyIdentifier' => 'Varchar(255)',
        'HierarchyDepth'      => 'Int',
        'SortOrder'           => 'Int'
    );
    /**
     * @var array
     */
    private static $indexes = array(
        'HierarchyIdentifier' => true
    );
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
     * @param boolean $skipChildrenField
     */
    public function setSkipChildrenField($skipChildrenField)
    {
        $this->skipChildrenField = $skipChildrenField;
    }
    /**
     *
     */
    public function onBeforeWrite()
    {
        if ($this->owner->SortOrder === null && (array_key_exists(
                    'ParentID',
                    $this->owner->toMap()
                ) || $this->owner->exists())
        ) {
            $this->owner->SortOrder = $this->getNextSortOrder();
        }
        $parent = $this->owner;
        $sorts = array(
            $parent->getSortOrderChar()
        );
        $titles = array(
            $parent->Title
        );
        $identifiers = array(
            $parent->Identifier
        );
        while ($parent->ParentID) {
            $parent = $parent->Parent();
            $sorts[] = $parent->getSortOrderChar();
            $titles[] = $parent->Title;
            $identifiers[] = $parent->Identifier;
        }
        $this->owner->HierarchySortOrder = implode('', array_reverse($sorts));
        $this->owner->HierarchyTitle = implode(' -> ', array_reverse($titles));
        $this->owner->HierarchyIdentifier = implode('/', array_reverse($identifiers));
        $this->owner->HierarchyDepth = count($identifiers) - 1;

        $children = $this->owner->Children();
        if ($children instanceof IteratorAggregate) {
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
        $fields->removeByName('HierarchySortOrder');
        $fields->removeByName('HierarchyTitle');
        $fields->removeByName('Parent');
        $fields->insertBefore(
            $fields->dataFieldByName('SortOrder'),
            'Identifier'
        );
    }
    /**
     * @return FieldSet
     */
    public function getLimitedCMSFields()
    {
        $this->setSkipChildrenField(true);
        $fields = $this->owner->getCMSFields();
        // TODO: This is a dependency
        $fields->replaceField(
            'HTML',
            new HtmlEditorField(
                'HTML',
                'Html'
            )
        );

        return $fields;
    }
    /**
     * @return string
     */
    public function getSortOrderChar()
    {
        $sortOrder = $this->owner->SortOrder + 33;
        $num = $sortOrder / 126;
        if ($num > 1) {
            return str_repeat(chr(126), floor($num)) . chr($sortOrder % 126);
        } else {
            return chr($sortOrder);
        }
    }
    /**
     * @return int|mixed
     */
    protected function getNextSortOrder()
    {
        $siblings = $this->getSiblings();
        if ($siblings instanceof DataList) {
            return ($do = $siblings->last()) instanceof DataObject ? $do->SortOrder + 1 : 0;
        } else {
            return 0;
        }
    }
    /**
     * @return DataList
     */
    protected function getSiblings()
    {
        return DataList::create($this->owner->ClassName)
            ->filter('ParentID', $this->owner->ParentID)
            ->sort('SortOrder', 'ASC');
    }
}
