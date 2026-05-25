<?php

namespace Heyday\AdaptiveContent\Tests;

use Heyday\AdaptiveContent\Extensions\AdaptiveContent;
use Heyday\AdaptiveContent\Tests\Support\AdaptiveContentTest_Item;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\Dev\SapphireTest;

class AdaptiveContentTest extends SapphireTest
{
    protected $usesDatabase = false;

    public function testGetExtraConfigDefinesSchema(): void
    {
        $config = AdaptiveContent::get_extra_config(
            AdaptiveContentTest_Item::class,
            AdaptiveContent::class,
            []
        );

        $this->assertArrayHasKey('db', $config);
        $this->assertSame('Varchar(255)', $config['db']['Identifier']);
        $this->assertSame('Varchar(255)', $config['db']['Title']);
        $this->assertSame(Image::class, $config['has_one']['LeadImage']);
        $this->assertSame(File::class, $config['has_one']['LeadFile']);
        $this->assertSame(Image::class, $config['many_many']['Images']);
    }

    public function testOnBeforeWriteGeneratesIdentifierFromTitle(): void
    {
        $item = AdaptiveContentTest_Item::create([
            'Title' => 'My Test Title',
            'Identifier' => '',
        ]);
        $item->extend('onBeforeWrite');

        $this->assertSame('my-test-title', $item->Identifier);
    }

    public function testOnBeforeWritePreservesExistingIdentifier(): void
    {
        $item = AdaptiveContentTest_Item::create([
            'Title' => 'Changed Title',
            'Identifier' => 'custom-id',
        ]);
        $item->extend('onBeforeWrite');

        $this->assertSame('custom-id', $item->Identifier);
    }

    public function testGetGeneratedIdentifierWithExplicitTitle(): void
    {
        $item = AdaptiveContentTest_Item::create(['Title' => 'Hello World']);
        $extension = $item->getExtensionInstance(AdaptiveContent::class);
        $this->assertInstanceOf(AdaptiveContent::class, $extension);
        $extension->setOwner($item);

        $this->assertSame('hello-world', $extension->getGeneratedIdentifier());
        $this->assertSame('custom-slug', $extension->getGeneratedIdentifier('Custom Slug'));
    }

}
