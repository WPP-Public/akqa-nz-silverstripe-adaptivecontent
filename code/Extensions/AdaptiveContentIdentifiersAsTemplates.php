<?php

/**
 * Class AdaptiveContentIdentifiersAsTemplates
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
    public function getAvailableSecondaryIdentifiers(array $map = array())
    {
        $className = strtolower($this->owner->ClassName);
        $currentTheme = Config::inst()->get('SSViewer', 'theme');
        $templates = SS_TemplateLoader::instance()->getManifest()->getTemplates();
        $availableTemplates = array();

        foreach ($templates as $templateName => $template) {
            if (
                fnmatch($className . '_*', $templateName)
                && isset($template['themes'])
                && isset($template['themes'][$currentTheme])
            ) {
                $templateName = isset($template['themes'][$currentTheme]['Includes'])
                    ? $template['themes'][$currentTheme]['Includes']
                    : $template['themes'][$currentTheme]['Layout'];
                $templateName = substr(basename($templateName), strlen($className) + 1, -3);
                $availableTemplates[$templateName] = $templateName;
            }
        }

        $availableTemplates = is_array($availableTemplates) ? $availableTemplates : array();

        foreach ($availableTemplates as $key => $value) {
            $availableTemplates[$key] = isset($map[$value]) ? $map[$value] : $value;
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
        if (!empty($this->owner->Identifier)) {
            $templates[] = $this->owner->ClassName . '_' . $this->owner->Identifier;
        }
        if (!empty($this->owner->SecondaryIdentifier)) {
            $templates[] = $this->owner->ClassName . '_' . $this->owner->SecondaryIdentifier;
        }
        if (Config::inst()->forClass(__CLASS__)->get('HasDefault')) {
            $templates[] = $this->owner->ClassName;
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
}
