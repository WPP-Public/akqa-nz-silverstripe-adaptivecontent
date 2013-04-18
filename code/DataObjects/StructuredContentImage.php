<?php

/**
 * Class StructuredContentImage
 */
class StructuredContentImage extends DataObject
{
    /**
     * @var array
     */
    static $db = array(
        'Caption' => 'Text'
    );
    /**
     * @var array
     */
    static $has_one = array(
        'Image' => 'Image',
        'Parent' => 'StructuredContent'
    );
    public function getCMSFields($params = null)
    {
        $fields = parent::getCMSFields($params);
        $fields->removeByName('ParentID');
        $fields->removeByName('SortOrder');
        return $fields;
    }
}