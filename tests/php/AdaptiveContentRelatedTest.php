<?php

namespace Heyday\AdaptiveContent\Tests;

use Heyday\AdaptiveContent\Extensions\AdaptiveContentRelated;
use Heyday\AdaptiveContent\Tests\Support\AdaptiveContentTest_Page;
use Heyday\AdaptiveContent\Tests\Support\AdaptiveContentTest_RelatedItem;
use SilverStripe\Dev\SapphireTest;

class AdaptiveContentRelatedTest extends SapphireTest
{
    protected $usesDatabase = false;

    public function testConstructorStoresRelationClass(): void
    {
        $extension = new AdaptiveContentRelated(AdaptiveContentTest_Page::class);

        $this->assertSame(AdaptiveContentRelated::class, $extension::class);
    }

    public function testGetExtraConfigDefinesRelationSchema(): void
    {
        $config = AdaptiveContentRelated::get_extra_config(
            AdaptiveContentTest_RelatedItem::class,
            AdaptiveContentRelated::class,
            [AdaptiveContentTest_Page::class]
        );

        $this->assertSame('Varchar(255)', $config['db']['RelationLink']);
        $this->assertSame(AdaptiveContentTest_Page::class, $config['has_one']['Relation']);
    }

}
