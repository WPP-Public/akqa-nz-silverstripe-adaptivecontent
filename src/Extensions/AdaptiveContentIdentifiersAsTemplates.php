<?php

namespace Heyday\AdaptiveContent\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\DropdownField;

/**
 * Secondary identifiers as templates
 *
 * This extension renders the SecondaryIdentifier field from the AdaptiveContent extension
 * as a dropdown list of templates. It was primarily written for use with pre 1.0 versions
 * of the heyday/silverstripe-slices module, so may not be much use on its own.
 */
class AdaptiveContentIdentifiersAsTemplates extends DataExtension
{
    private static $has_default_template = true;

    private static $secondary_identifiers = [];

    public function populateDefaults()
    {
        if (!$this->owner->config()->get('has_default_template')) {
            $this->owner->SecondaryIdentifier = reset($this->owner->config()->get('secondary_identifiers'));
        }
    }


    public function updateCMSFields(FieldList $fields)
    {
        $fields->replaceField(
            'SecondaryIdentifier',
            $field = new DropdownField(
                'SecondaryIdentifier',
                'Secondary Identifier',
                $this->owner->config()->get('secondary_identifiers')
            )
        );

        if ($this->owner->config()->get('HasDefault')) {
            $field->setHasEmptyDefault(true);
            $field->setEmptyString('Default');
        }
    }

    /**
     * If classname "ComponentSlice", Identifier is "my-slice", and SecondaryIdentifier is "TwoColumn"
     * then output: array("ComponentSlice_my-slice", "ComponentSlice_TwoColumn")
     *
     * If HasDefault is true, output: array("ComponentSlice_my-slice", "ComponentSlice_TwoColumn", "ComponentSlice")
     * @return array
     */
    public function getTemplates()
    {
        $templates = array();
        $prefix = $this->owner->getTemplateClass();

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
    public function getTemplateClass()
    {
        return $this->owner->ClassName;
    }
}
