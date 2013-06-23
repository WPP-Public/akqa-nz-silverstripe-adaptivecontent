<?php

/**
 * Class AdaptiveContent
 */
class AdaptiveContent extends DataExtension
{
    /**
     * @var array
     */
    private static $db = array(
        'Identifier'          => 'Varchar(255)',
        'SecondaryIdentifier' => 'Varchar(255)',
        'Title'               => 'Varchar(255)',
        'SubTitle'            => 'Varchar(255)',
        'Teaser'              => 'Text',
        'ShortTeaser'         => 'Text',
        'Content'             => 'HTMLText'
    );
    /**
     * @var array
     */
    private static $has_one = array(
        'LeadImage' => 'AdaptiveContentImage'
    );
    /**
     * @var array
     */
    private static $has_many = array(
        'Images' => 'AdaptiveContentImage'
    );
    /**
     * Generates identifier from title, when identifier doesn't exist
     */
    public function onBeforeWrite()
    {
        if ($this->owner->Identifier == '') {
            $this->owner->Identifier = $this->getGeneratedIdentifier();
        }
    }
    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->Identifier == '') {
            $fields->removeByName('Identifier');
        } else {
            $fields->makeFieldReadonly('Identifier');
        }
    }
    /**
     * @param  bool $title
     * @return string
     */
    public function getGeneratedIdentifier($title = false)
    {
        return URLSegmentFilter::create()->filter($title ? $title : $this->owner->Title);
    }
}
