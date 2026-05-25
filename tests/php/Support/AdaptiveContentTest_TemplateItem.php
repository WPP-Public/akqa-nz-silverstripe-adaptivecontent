<?php

namespace Heyday\AdaptiveContent\Tests\Support;

use Heyday\AdaptiveContent\Extensions\AdaptiveContent;
use Heyday\AdaptiveContent\Extensions\AdaptiveContentIdentifiersAsTemplates;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * @method list<string> getTemplates()
 */
class AdaptiveContentTest_TemplateItem extends DataObject implements TestOnly
{
    private static $table_name = 'AdaptiveContentTest_TemplateItem';

    private static $extensions = [
        AdaptiveContent::class,
        AdaptiveContentIdentifiersAsTemplates::class,
    ];

    private static $has_default_template = false;

    private static $secondary_identifiers = [
        'OneColumn' => 'One column',
        'TwoColumn' => 'Two column',
    ];

    private static $HasDefault = true;
}
