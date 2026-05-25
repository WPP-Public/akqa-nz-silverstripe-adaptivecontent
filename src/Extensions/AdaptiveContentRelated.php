<?php

namespace Heyday\AdaptiveContent\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Forms\FieldList;

/**
 * @extends Extension<\SilverStripe\ORM\DataObject>
 */
class AdaptiveContentRelated extends Extension
{
    private string $relationClass;

    /**
     * @param string $relationClass
     */
    public function __construct($relationClass)
    {
        $this->relationClass = $relationClass;
        parent::__construct();
    }

    /**
     * @param class-string $class
     * @param class-string $extension
     * @param mixed $args
     * @return array<string, mixed>
     */
    public static function get_extra_config($class, $extension, $args): array
    {
        return [
            'db' => [
                'RelationLink' => 'Varchar(255)',
            ],
            'has_one' => [
                'Relation' => $args[0],
            ],
        ];
    }

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields): void
    {
        $fields->replaceField(
            'RelationID',
            TreeDropdownField::create(
                'RelationID',
                'Relation',
                $this->relationClass
            )
        );
    }
}
