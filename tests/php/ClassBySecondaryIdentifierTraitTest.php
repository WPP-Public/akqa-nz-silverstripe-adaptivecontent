<?php

namespace Heyday\AdaptiveContent\Tests;

use Heyday\AdaptiveContent\Tests\Support\AdaptiveContentTest_BaseSlice;
use Heyday\AdaptiveContent\Tests\Support\AdaptiveContentTest_TwoColumnSlice;
use ReflectionMethod;
use SilverStripe\Dev\SapphireTest;

class ClassBySecondaryIdentifierTraitTest extends SapphireTest
{
    protected $usesDatabase = false;

    public function testGetTemplateClassReturnsDeclaringClass(): void
    {
        $slice = AdaptiveContentTest_BaseSlice::create();

        $this->assertSame(AdaptiveContentTest_BaseSlice::class, $slice->getTemplateClass());
    }

    public function testSetClassNameBySecondaryIdentifierMapsToConfiguredClass(): void
    {
        $slice = AdaptiveContentTest_BaseSlice::create([
            'SecondaryIdentifier' => 'TwoColumn',
        ]);
        $method = new ReflectionMethod(AdaptiveContentTest_BaseSlice::class, 'setClassNameBySecondaryIdentifier');
        $method->invoke($slice);

        $this->assertSame(AdaptiveContentTest_TwoColumnSlice::class, $slice->ClassName);
    }

    public function testSetClassNameBySecondaryIdentifierResetsToBaseClassWhenUnmapped(): void
    {
        $slice = AdaptiveContentTest_BaseSlice::create([
            'SecondaryIdentifier' => 'Unknown',
        ]);
        $method = new ReflectionMethod(AdaptiveContentTest_BaseSlice::class, 'setClassNameBySecondaryIdentifier');
        $method->invoke($slice);

        $this->assertSame(AdaptiveContentTest_BaseSlice::class, $slice->ClassName);
    }
}
