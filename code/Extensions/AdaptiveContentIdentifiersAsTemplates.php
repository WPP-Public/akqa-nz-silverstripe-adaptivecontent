<?php

/**
 * Class AdaptiveContentIdentifiersAsTemplates
 */
class AdaptiveContentIdentifiersAsTemplates extends DataExtension
{
    /**
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

        $field->setHasEmptyDefault(true);
        $field->setEmptyString('Default');
    }
    /**
     * @param array $map
     * @return array
     */
    public function getAvailableSecondaryIdentifiers(array $map)
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
        $map = is_array($map) ? $map : array();

        foreach ($availableTemplates as $key => $value) {
            $availableTemplates[$key] = isset($map[$value]) ? $map[$value] : $value;
        }

        return $availableTemplates;
    }
    /**
     * @return SSViewer
     */
    public function getSSViewer()
    {
        return new SSViewer(
            $this->getTemplates()
        );
    }
    /**
     * @return array
     */
    public function getTemplates()
    {
        $templates = array(
            $this->owner->ClassName . '_' . $this->owner->Identifier
        );
        if (!empty($this->owner->SecondaryIdentifier)) {
            $templates[] = $this->owner->ClassName . '_' . $this->owner->SecondaryIdentifier;
        }
        $templates[] = $this->owner->ClassName;
        return $templates;
    }
}
