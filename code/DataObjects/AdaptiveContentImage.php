<?php

/**
 * Class AdaptiveContentImage
 */
class AdaptiveContentImage extends Image
{
    /**
     * @var array
     */
    private static $db = array(
        'Caption' => 'Text',
        'Sort'    => 'Int'
    );
    /**
     * @var string
     */
    private static $default_sort = 'Sort ASC';
    /**
     * @param  null $params
     * @return FieldSet
     */
    public function getCMSFields($params = null)
    {
        $fields = parent::getCMSFields($params);

        $fields->addFieldToTab('Root.Main', new TextareaField('Caption'));

        return $fields;
    }
}
