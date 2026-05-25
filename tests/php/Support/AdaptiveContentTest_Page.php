<?php

namespace Heyday\AdaptiveContent\Tests\Support;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class AdaptiveContentTest_Page extends DataObject implements TestOnly
{
    private static $table_name = 'AdaptiveContentTest_Page';

    private static $db = [
        'Title' => 'Varchar(255)',
    ];
}
