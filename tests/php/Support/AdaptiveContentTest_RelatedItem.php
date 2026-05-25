<?php

namespace Heyday\AdaptiveContent\Tests\Support;

use Heyday\AdaptiveContent\Extensions\AdaptiveContentRelated;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class AdaptiveContentTest_RelatedItem extends DataObject implements TestOnly
{
    private static $table_name = 'AdaptiveContentTest_RelatedItem';

    private static $extensions = [
        AdaptiveContentRelated::class . "('" . AdaptiveContentTest_Page::class . "')",
    ];
}
