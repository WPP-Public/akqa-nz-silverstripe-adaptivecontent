<?php

namespace Heyday\AdaptiveContent\Tests\Support;

use Heyday\AdaptiveContent\Extensions\AdaptiveContent;
use Heyday\AdaptiveContent\Extensions\AdaptiveContentHierarchy;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class AdaptiveContentTest_HierarchyItem extends DataObject implements TestOnly
{
    private static $table_name = 'AdaptiveContentTest_HierarchyItem';

    private static $extensions = [
        AdaptiveContent::class,
        AdaptiveContentHierarchy::class,
    ];
}
