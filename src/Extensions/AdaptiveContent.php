<?php

namespace Heyday\AdaptiveContent\Extensions;

use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\Parsers\URLSegmentFilter;

/**
 * Adaptive content fields for slice-like DataObjects.
 *
 * SilverStripe 6+ does not expose extension private static config to {@see ExtensionMiddleware}
 * the same way as core modules, so schema must be supplied via {@see get_extra_config()}.
 */
class AdaptiveContent extends DataExtension
{
    /**
     * @param class-string $class Owner class name
     * @param class-string $extensionClass This extension FQCN
     * @param mixed $args Constructor args from YAML (if any)
     * @return array<string, mixed>
     */
    public static function get_extra_config($class, $extensionClass, $args)
    {
        return [
            'db' => [
                'Identifier' => 'Varchar(255)',
                'SecondaryIdentifier' => 'Varchar(255)',
                'TertiaryIdentifier' => 'Varchar(255)',
                'Title' => 'Varchar(255)',
                'SubTitle' => 'Varchar(255)',
                'Teaser' => 'Text',
                'ShortTeaser' => 'Text',
                'Content' => 'HTMLText',
                'SecondaryContent' => 'HTMLText',
            ],
            'has_one' => [
                'LeadImage' => Image::class,
                'SecondaryImage' => Image::class,
                'LeadFile' => File::class,
            ],
            'many_many' => [
                'Images' => Image::class,
                'Files' => File::class,
            ],
        ];
    }

    /**
     * Generates identifier from title, when identifier doesn't exist
     */
    public function onBeforeWrite()
    {
        if ($this->owner->Identifier == '') {
            $this->owner->Identifier = $this->getGeneratedIdentifier();
        }
    }

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        if ($fields->dataFieldByName('Identifier')) {
            if ($this->owner->Identifier == '') {
                $fields->removeByName('Identifier');
            } else {
                $fields->makeFieldReadonly('Identifier');
            }
        }

        $fields->removeByName([
            'Images',
            'Files',
        ]);

        if ($this->owner->hasMethod('Images')) {
            $fields->addFieldToTab(
                'Root.Images',
                UploadField::create('Images', 'Images', $this->owner->Images())
            );
        }

        if ($this->owner->hasMethod('Files')) {
            $fields->addFieldToTab(
                'Root.Files',
                UploadField::create('Files', 'Files', $this->owner->Files())
            );
        }
    }

    /**
     * @param bool $title
     */
    public function getGeneratedIdentifier($title = false)
    {
        return URLSegmentFilter::create()->filter($title ? $title : $this->owner->Title);
    }
}
