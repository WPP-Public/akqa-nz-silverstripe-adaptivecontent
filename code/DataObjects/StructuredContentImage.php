<?php

class StructuredContentImage extends DataObject
{
    static $db = array(
        'Caption' => 'Text'
    );
    static $has_one = array(
        'Image' => 'Image',
        'Parent' => 'StructuredContent'
    );
}