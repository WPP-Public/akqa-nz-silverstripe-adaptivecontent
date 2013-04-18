<?php

class StructuredContentModelAdmin extends RemodelAdmin
{
    public static $managed_models = array(
        'StructuredContent'
    );
    public static $url_segment = 'structured-content';
    public static $menu_title = 'Structured Content';
}