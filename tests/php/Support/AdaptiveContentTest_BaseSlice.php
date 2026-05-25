<?php

namespace Heyday\AdaptiveContent\Tests\Support;

use Heyday\AdaptiveContent\Traits\ClassBySecondaryIdentifierTrait;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class AdaptiveContentTest_BaseSlice extends DataObject implements TestOnly
{
    use ClassBySecondaryIdentifierTrait;

    private static $table_name = 'AdaptiveContentTest_BaseSlice';

    private static $db = [
        'SecondaryIdentifier' => 'Varchar(255)',
    ];

    private static $secondaryIdentifierClassMap = [
        'TwoColumn' => AdaptiveContentTest_TwoColumnSlice::class,
    ];
}
