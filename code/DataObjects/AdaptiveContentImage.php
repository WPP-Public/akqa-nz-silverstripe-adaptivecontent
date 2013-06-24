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
        'Sort'    => 'Int'
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
     * @var array
     */
    private static $summary_fields = array(
        'Image' => 'Image',
        'Caption' => 'Caption'
    );
    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = new FieldList();

        $fields->push(new TextareaField('Caption'));
        $fields->push(new UploadField('Image', 'Image'));

        return $fields;
    }
}
