<?php

/**
 * Class StructuredContent
 */
class StructuredContent extends DataObject
{
    /**
     * @var array
     */
    public static $extensions = array(
        "Hierarchy",
        "Versioned('Stage', 'Live')",
    );
    /**
     * @var array
     */
    public static $db = array(
        'Title' => 'Varchar(255)',
        'SubTitle' => 'Varchar(255)',
        'Teaser' => 'Text',
        'ShortTeaser' => 'Text'
    );
    /**
     * @var array
     */
    public static $has_one = array(
        'LeadImage' => 'StructuredContentImage'
    );
    /**
     * @var array
     */
    public static $has_many = array(
        'HTMLs' => 'StructuredContentHTML',
        'Images' => 'StructuredContentImage',
        'Children' => 'StructuredContent'
    );
}