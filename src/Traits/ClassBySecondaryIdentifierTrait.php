<?php

namespace Heyday\AdaptiveContent\SilverStripe\Traits;

/**
 * Allow setting the class of a DataObject using AdaptiveContent's SecondaryIdentifier field
 *
 * The config key secondaryIdentifierClassMap is used to map classes to templates.
 * If a template doesn't have a mapping defined, the name of the class using this trait will be used.
 *
 * Use in config:
 *
 * secondaryIdentifierClassMap:
 *   MyTemplate: MyTemplateClass
 *
 * @package Heyday\AdaptiveContent\SilverStripe\Traits
 */
trait ClassBySecondaryIdentifierTrait
{
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // Update class name if it needs to change for the selected identifier
        $this->setClassNameBySecondaryIdentifier();
    }

    public function getTemplateClass()
    {
        return __CLASS__;
    }

    /**
     * Change class name based on the mapping in config key secondaryIdentifierClassMap
     */
    protected function setClassNameBySecondaryIdentifier()
    {
        $classMap = $this->config()->get('secondaryIdentifierClassMap');

        if (is_array($classMap) && isset($classMap[$this->SecondaryIdentifier])) {
            $this->setClassName($classMap[$this->SecondaryIdentifier]);
            if($this->unsavedRelations) {
                foreach($this->unsavedRelations as $name => $list) {
                    if(!$this->hasMethod($name)) {
                        unset($this->unsavedRelations[$name]);
                    }
                }
            }
        } else {
            $this->setClassName(__CLASS__);
        }
    }
} 