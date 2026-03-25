<?php

namespace Heyday\AdaptiveContent\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Forms\FieldList;

/**
 * Class AdaptiveContentRelated
 */
class AdaptiveContentRelated extends DataExtension
{
    private $relationClass;

    /**
     * @param string $relationClass
     */
    public function __construct($relationClass)
    {
        $this->relationClass = $relationClass;
        parent::__construct();
    }

    /**
     * @param $class
     * @param $extension
     * @param $args
     * @return array
     */
    public static function get_extra_config($class, $extension, $args)
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
    public function updateCMSFields(FieldList $fields)
    {
        $fields->replaceField(
            'RelationID',
            new TreeDropdownField(
                'RelationID',
                'Relation',
                $this->relationClass
            )
        );
    }
}
