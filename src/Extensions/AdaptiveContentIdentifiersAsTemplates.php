<?php

namespace Heyday\AdaptiveContent\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\DropdownField;

/**
 * Secondary identifiers as templates
 *
 * This extension renders the SecondaryIdentifier field from the AdaptiveContent extension
 * as a dropdown list of templates. It was primarily written for use with pre 1.0 versions
 * of the heyday/silverstripe-slices module, so may not be much use on its own.
 *
 * @extends Extension<\SilverStripe\ORM\DataObject>
 */
class AdaptiveContentIdentifiersAsTemplates extends Extension
{
    private static bool $has_default_template = true;

    private static array $secondary_identifiers = [];

    /**
     * @return void
     */
    public function onAfterPopulateDefaults(): void
    {
        $owner = $this->owner;
        if (!$owner->config()->get('has_default_template')) {
            $identifiers = $owner->config()->get('secondary_identifiers');
            $identifier = reset($identifiers);

            if ($identifier) {
                $owner->SecondaryIdentifier = $identifier;
            } else {
                $owner->SecondaryIdentifier = '';
            }
        }
    }


    public function updateCMSFields(FieldList $fields): void
    {
        $owner = $this->owner;

        $fields->replaceField(
            'SecondaryIdentifier',
            $field = DropdownField::create(
                'SecondaryIdentifier',
                'Secondary Identifier',
                $owner->config()->get('secondary_identifiers') ?? []
            )
        );

        if ($owner->config()->get('HasDefault')) {
            $field->setHasEmptyDefault(true);
            $field->setEmptyString('Default');
        }
    }

    /**
     * If classname "ComponentSlice", Identifier is "my-slice", and SecondaryIdentifier is "TwoColumn"
     * then output: array("ComponentSlice_my-slice", "ComponentSlice_TwoColumn")
     *
     * If HasDefault is true, output: array("ComponentSlice_my-slice", "ComponentSlice_TwoColumn", "ComponentSlice")
     * @return list<string>
     */
    public function getTemplates(): array
    {
        $templates = array();
        $prefix = $this->owner->hasMethod('getTemplateClass')
            ? $this->owner->getTemplateClass()
            : $this->getTemplateClass();

        if (!empty($this->owner->Identifier)) {
            $templates[] = $prefix . '_' . $this->owner->Identifier;
        }
        if (!empty($this->owner->SecondaryIdentifier)) {
            $templates[] = $prefix . '_' . $this->owner->SecondaryIdentifier;
        }
        if ($this->owner->config()->get('HasDefault')) {
            $templates[] = $prefix;
        }
        return $templates;
    }

    /**
     * Return the class name to prefix templates with
     *
     * This method should be called through $this->owner within this extension so that it
     * can be overridden in the owner class, or by extensions/traits on the owner class.
     *
     * @return string
     */
    public function getTemplateClass(): string
    {
        return $this->owner->ClassName;
    }
}
