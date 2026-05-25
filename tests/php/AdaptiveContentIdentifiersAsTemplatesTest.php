<?php

namespace Heyday\AdaptiveContent\Tests;

use Heyday\AdaptiveContent\Extensions\AdaptiveContentIdentifiersAsTemplates;
use Heyday\AdaptiveContent\Tests\Support\AdaptiveContentTest_TemplateItem;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;

class AdaptiveContentIdentifiersAsTemplatesTest extends SapphireTest
{
    protected $usesDatabase = false;

    public function testUpdateCMSFieldsReplacesSecondaryIdentifierWithDropdown(): void
    {
        $item = AdaptiveContentTest_TemplateItem::create();
        $fields = FieldList::create(
            TextField::create('SecondaryIdentifier', 'Secondary Identifier')
        );
        $extension = $item->getExtensionInstance(AdaptiveContentIdentifiersAsTemplates::class);
        $this->assertInstanceOf(AdaptiveContentIdentifiersAsTemplates::class, $extension);
        $extension->setOwner($item);
        $extension->updateCMSFields($fields);

        $field = $fields->dataFieldByName('SecondaryIdentifier');
        $this->assertInstanceOf(DropdownField::class, $field);
        $this->assertTrue($field->getHasEmptyDefault());
        $this->assertSame('Default', $field->getEmptyString());
    }

    public function testGetTemplatesIncludesIdentifierSecondaryAndDefault(): void
    {
        $item = AdaptiveContentTest_TemplateItem::create([
            'Identifier' => 'my-slice',
            'SecondaryIdentifier' => 'TwoColumn',
        ]);

        $templates = $item->getTemplates();

        $this->assertSame(
            [
                AdaptiveContentTest_TemplateItem::class . '_my-slice',
                AdaptiveContentTest_TemplateItem::class . '_TwoColumn',
                AdaptiveContentTest_TemplateItem::class,
            ],
            $templates
        );
    }

    public function testGetTemplateClassReturnsOwnerClassName(): void
    {
        $item = AdaptiveContentTest_TemplateItem::create();
        $extension = $item->getExtensionInstance(AdaptiveContentIdentifiersAsTemplates::class);
        $this->assertInstanceOf(AdaptiveContentIdentifiersAsTemplates::class, $extension);
        $extension->setOwner($item);

        $this->assertSame(AdaptiveContentTest_TemplateItem::class, $extension->getTemplateClass());
    }
}
