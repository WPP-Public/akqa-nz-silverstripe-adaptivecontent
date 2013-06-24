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
        $fields->replaceField(
            'SecondaryIdentifier',
            $field = new DropdownField(
                'SecondaryIdentifier',
                'Secondary Identifier',
                $this->getAvailableSecondaryIdentifiers()
            )
        );

        $field->setHasEmptyDefault(true);
        $field->setEmptyString('Default');
    }
    /**
     * @return array
     */
    public function getAvailableSecondaryIdentifiers()
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
                $availableTemplates[] = substr(basename($templateName), strlen($className) + 1, -3);
            }
        }

        return count($availableTemplates) > 0 ? array_combine($availableTemplates, $availableTemplates) : array();
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
