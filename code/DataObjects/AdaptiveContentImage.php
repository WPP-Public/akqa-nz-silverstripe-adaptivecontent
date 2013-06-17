<?php

/**
 * Class AdaptiveContentImage
 */
class AdaptiveContentImage extends DataObject
{
    /**
     * @var array
     */
    private static $db = array(
        'Caption' => 'Text',
        'Sort' => 'Int'
    );
    /**
     * @var array
     */
    private static $has_one = array(
        'Image' => 'Image',
        'Parent' => 'DataObject'
    );
    /**
     * @var string
     */
    private static $default_sort = 'Sort ASC';
    /**
     * @param  null     $params
     * @return FieldSet
     */
    public function getCMSFields($params = null)
    {
        $fields = parent::getCMSFields($params);
        $fields->removeByName('ParentID');
        $fields->removeByName('Sort');

        return $fields;
    }
}
