<?php

namespace Heyday\AdaptiveContent\Tests\Support;

use Heyday\AdaptiveContent\Extensions\AdaptiveContent;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class AdaptiveContentTest_Item extends DataObject implements TestOnly
{
    private static $table_name = 'AdaptiveContentTest_Item';

    private static $extensions = [
        AdaptiveContent::class,
    ];
}
