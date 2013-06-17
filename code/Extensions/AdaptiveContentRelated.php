<?php

/**
 * Class AdaptiveContentRelated
 */
class AdaptiveContentRelated extends DataExtension
{
    /**
     * @var
     */
    private $relationClass;
    /**
     * @param $relationClass
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
        return array(
            'has_one' => array(
                'Relation' => $args[0]
            )
        );
    }
}