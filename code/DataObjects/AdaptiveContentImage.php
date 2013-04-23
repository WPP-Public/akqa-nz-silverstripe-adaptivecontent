<?php

/**
 * Class AdaptiveContentImage
 */
class AdaptiveContentImage extends DataObject
{
    /**
     * @var array
     */
    public static $extensions = array(
        'SortableDataObject'
    );
    /**
     * @var array
     */
    public static $db = array(
        'Caption' => 'Text'
    );
    /**
     * @var array
     */
    public static $has_one = array(
        'Image' => 'Image',
        'Parent' => 'DataObject'
    );
    /**
     * @param  null     $params
     * @return FieldSet
     */
    public function getCMSFields($params = null)
    {
        $fields = parent::getCMSFields($params);
        $fields->removeByName('ParentID');
        $fields->removeByName('SortOrder');

        return $fields;
    }
}
