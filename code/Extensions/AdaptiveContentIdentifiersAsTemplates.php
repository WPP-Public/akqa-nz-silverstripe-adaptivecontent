<?php

/**
 * Secondary identifiers as templates
 *
 * This extension renders the SecondaryIdentifier field from the AdaptiveContent extension
 * as a dropdown list of templates. It was primarily written for use with pre 1.0 versions
 * of the heyday/silverstripe-slices module, so may not be much use on its own.
 */
class AdaptiveContentIdentifiersAsTemplates extends DataExtension
{
    /**
     *  If not is has default mode then populate defaults
     */
    public function populateDefaults()
    {
        if (!Config::inst()->forClass(__CLASS__)->get('HasDefault')) {
            $identifiers = $this->getAvailableSecondaryIdentifiers();
            $this->owner->SecondaryIdentifier = reset($identifiers);
        }
    }

    /**
     * Adds a field the the cms allowing the user to choose a secondary identifier based on templates
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        /** @var Config_ForClass $config */
        $config = $this->owner->config();

        $fields->replaceField(
            'SecondaryIdentifier',
            $field = new DropdownField(
                'SecondaryIdentifier',
                'Secondary Identifier',
                $this->getAvailableSecondaryIdentifiers(
                    $config->get(
                        'secondaryIdentifierAsTemplatesMap',
                        Config::UNINHERITED
                    )
                )
            )
        );

        if (Config::inst()->forClass(__CLASS__)->get('HasDefault')) {
            $field->setHasEmptyDefault(true);
            $field->setEmptyString('Default');
        }
    }

    /**
     * Finds all available template based on the ClassName
     * @param array $map
     * @return array
     */
    public function getAvailableSecondaryIdentifiers($map = null)
    {
        $prefix = strtolower($this->owner->getTemplateClass());
        $currentTheme = Config::inst()->get('SSViewer', 'theme');
        $templates = SS_TemplateLoader::instance()->getManifest()->getTemplates();
        $availableTemplates = array();

        foreach ($templates as $templateName => $template) {
            if (
                fnmatch($prefix . '_*', $templateName)
                && isset($template['themes'])
                && isset($template['themes'][$currentTheme])
            ) {
                // SilverStripe transforms all template names to lowercase.
                // We want the original filename, so this needs to be extracted from the template file path
                $templateName = $this->getFirstLeafNode($template);
                $templateName = substr(basename($templateName), strlen($prefix) + 1, -3);
                $availableTemplates[$templateName] = $templateName;
            }
        }

        $availableTemplates = is_array($availableTemplates) ? $availableTemplates : array();

        if (is_array($map)) {
            foreach ($availableTemplates as $key => $value) {
                $availableTemplates[$key] = isset($map[$value]) ? $map[$value] : $value;
            }
        }

        return $availableTemplates;
    }

    /**
     * Returns the secondary identifier in a nicer format if specified in config "secondaryIdentifierAsTemplatesMap"
     * @return string
     */
    public function getSecondaryIdentifierNice()
    {
        /** @var Config_ForClass $config */
        $config = $this->owner->config();

        $identifiersMap = $config->get(
            'secondaryIdentifierAsTemplatesMap',
            Config::UNINHERITED
        );

        return isset($identifiersMap[$this->owner->SecondaryIdentifier])
            ? $identifiersMap[$this->owner->SecondaryIdentifier]
            : $this->owner->SecondaryIdentifier;
    }

    /**
     * Tries to get an SSViewer based on the current configuration
     * @throws Exception
     * @return SSViewer
     */
    public function getSSViewer()
    {
        return new SSViewer(
            $this->getTemplate()
        );
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
        if (Config::inst()->forClass($prefix)->get('HasDefault')) {
            $templates[] = $prefix;
        }
        return $templates;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getTemplate()
    {
        $templates = SS_TemplateLoader::instance()->findTemplates(
            $tryTemplates = $this->getTemplates(), Config::inst()->get('SSViewer', 'theme')
        );

        if (!$templates) {
            throw new Exception(
                'Can\'t find a template from list: "'.implode('", "', $tryTemplates).'"'
            );
        }

        return reset($templates);
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

    /**
     * Given a set of nested arrays, return the first leaf encountered
     *
     * @param string[] $tree
     * @return mixed
     */
    protected function getFirstLeafNode(array $tree)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($tree),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach($iterator as $value) {
            return $value;
        }
    }
}
