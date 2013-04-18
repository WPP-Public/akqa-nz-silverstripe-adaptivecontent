<?php

class StructuredContentHTML extends DataObject
{
    static $db = array(
        'HTML' => 'HTMLText'
    );
    static $has_one = array(
        'Parent' => 'StructuredContent'
    );
}