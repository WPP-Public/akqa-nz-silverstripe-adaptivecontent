<?php

class StructuredContent extends DataObject
{
    static $extensions = array(
        "Hierarchy",
        "Versioned('Stage', 'Live')",
    );
    public static $db = array(
        'Title' => 'Varchar(255)',
        'SubTitle' => 'Varchar(255)',
        'Teaser' => 'Text',
        'ShortTeaser' => 'Text'
    );
    public static $has_one = array(
        'LeadImage' => 'StructuredContentImage'
    );
    public static $has_many = array(
        'HTMLs' => 'StructuredContentHTML',
        'Images' => 'StructuredContentImage',
        'Children' => 'StructuredContent'
    );
}