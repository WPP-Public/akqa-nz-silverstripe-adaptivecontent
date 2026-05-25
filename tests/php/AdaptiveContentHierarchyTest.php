<?php

namespace Heyday\AdaptiveContent\Tests;

use Heyday\AdaptiveContent\Extensions\AdaptiveContentHierarchy;
use Heyday\AdaptiveContent\Tests\Support\AdaptiveContentTest_HierarchyItem;
use SilverStripe\Dev\SapphireTest;

class AdaptiveContentHierarchyTest extends SapphireTest
{
    protected $usesDatabase = false;

    public function testGetExtraConfigDefinesParentChildRelations(): void
    {
        $config = AdaptiveContentHierarchy::get_extra_config(
            AdaptiveContentTest_HierarchyItem::class,
            AdaptiveContentHierarchy::class,
            []
        );

        $this->assertSame(AdaptiveContentTest_HierarchyItem::class, $config['has_one']['Parent']);
        $this->assertSame(AdaptiveContentTest_HierarchyItem::class, $config['has_many']['Children']);
    }

    public function testUpdateSummaryFieldsAddsHierarchyTitle(): void
    {
        $fields = ['Title' => 'Title'];
        $extension = new AdaptiveContentHierarchy();

        $extension->updateSummaryFields($fields);

        $this->assertArrayHasKey('HierarchyTitle', $fields);
        $this->assertSame('Title', $fields['HierarchyTitle']);
    }

    public function testOnBeforeWriteSetsHierarchyFieldsForRootItem(): void
    {
        $item = AdaptiveContentTest_HierarchyItem::create([
            'Title' => 'Root',
            'Identifier' => 'root',
        ]);
        $item->extend('onBeforeWrite');

        $this->assertSame('Root', $item->HierarchyTitle);
        $this->assertSame('root', $item->HierarchyIdentifier);
        $this->assertSame(0, $item->HierarchyDepth);
    }
}
