<?php

namespace Heyday\AdaptiveContent\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\File;
use SilverStripe\Forms\FieldList;
use SilverStripe\View\Parsers\URLSegmentFilter;

/**
 * Class AdaptiveContent
 */
class AdaptiveContent extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'Identifier'          => 'Varchar(255)',
        'SecondaryIdentifier' => 'Varchar(255)',
        'TertiaryIdentifier'  => 'Varchar(255)',
        'Title'               => 'Varchar(255)',
        'SubTitle'            => 'Varchar(255)',
        'Teaser'              => 'Text',
        'ShortTeaser'         => 'Text',
        'Content'             => 'HTMLText',
        'SecondaryContent'    => 'HTMLText'
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'LeadImage'      => Image::class,
        'SecondaryImage' => Image::class,
        'LeadFile'       => File::class
    ];

    /**
     * @var array
     */
    private static $many_many = [
        'Images' => Image::class,
        'Files'  => File::class
    ];

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
        if ($this->owner->Identifier == '') {
            $fields->removeByName('Identifier');
        } else {
            $fields->makeFieldReadonly('Identifier');
        }

        $fields->removeByName([
            'Images',
            'Files'
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
     * @param  bool $title
     * @return string
     */
    public function getGeneratedIdentifier($title = false)
    {
        return URLSegmentFilter::create()->filter($title ? $title : $this->owner->Title);
    }
}