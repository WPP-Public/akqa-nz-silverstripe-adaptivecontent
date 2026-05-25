<?php

namespace Heyday\AdaptiveContent\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataList;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * @extends Extension<\SilverStripe\ORM\DataObject>
 */
class AdaptiveContentHierarchy extends Extension
{
    protected bool $skipChildrenField = false;

    private static array $db = [
        'HierarchyTitle'      => 'Varchar(255)',
        'HierarchyIdentifier' => 'Varchar(255)',
        'HierarchyDepth'      => 'Int',
        'Sort'                => 'Int'
    ];

    private static string $default_sort = 'Sort ASC';

    private static array $indexes = [
        'HierarchyIdentifier' => true
    ];

    /**
     * @param class-string $class
     * @param class-string $extension
     * @param mixed $args
     * @return array<string, mixed>
     */
    public static function get_extra_config($class, $extension, $args): array
    {
        return [
            'has_one'  => [
                'Parent' => $class
            ],
            'has_many' => [
                'Children' => $class
            ]
        ];
    }

    /**
     * @param array<string, string> $fields
     */
    public function updateSummaryFields(&$fields): void
    {
        $fields = array_merge(
            $fields,
            [
                'HierarchyTitle' => 'Title'
            ]
        );
    }


    public function onBeforeWrite(): void
    {
        $parent = $this->owner;

        $titles = [
            $parent->Title
        ];

        $identifiers = [
            $parent->Identifier
        ];

        while ($parent->ParentID) {
            $parent = $parent->Parent();
            $titles[] = $parent->Title;
            $identifiers[] = $parent->Identifier;
        }

        $this->owner->HierarchyTitle = implode(' -> ', array_reverse($titles));
        $this->owner->HierarchyIdentifier = implode('/', array_reverse($identifiers));
        $this->owner->HierarchyDepth = count($identifiers) - 1;

        if (!$this->owner->hasMethod('Children')) {
            return;
        }

        $children = $this->owner->Children();

        if ($children->count()) {
            foreach ($children as $child) {
                $child->write();
            }
        }
    }

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields): void
    {
        $fields->removeByName('HierarchyTitle');
        $fields->removeByName('Parent');

        if ($this->owner->hasMethod('Children')) {
            $fields->addFieldToTab(
                'Root.Children',
                GridField::create(
                    'Children',
                    '',
                    $this->owner->Children(),
                    $config = GridFieldConfig_RecordEditor::create()
                )
            );

            $config->addComponent(GridFieldOrderableRows::create('Sort'));
        }
    }

    /**
     * @return DataList<\SilverStripe\ORM\DataObject>
     */
    protected function getSiblings(): DataList
    {
        return DataList::create($this->owner->ClassName)
            ->filter('ParentID', $this->owner->ParentID)
            ->sort('Sort', 'ASC');
    }
}
