<?php

/**
 * Class AdaptiveContent
 */
class AdaptiveContent extends DataObjectDecorator
{
    /**
     * @return array
     */
    public function extraStatics()
    {
        return array(
            'db' => array(
                'Identifier' => 'Varchar(255)',
                'Title' => 'Varchar(255)',
                'SecondaryIdentifier' => 'Varchar(255)',
                'SubTitle' => 'Varchar(255)',
                'Teaser' => 'Text',
                'ShortTeaser' => 'Text',
                'HTML' => 'HTMLText'
            ),
            'has_one' => array(
                'LeadImage' => 'AdaptiveContentImage'
            ),
            'has_many' => array(
                'Images' => 'AdaptiveContentImage'
            )
        );
    }
    /**
     *
     */
    public function onBeforeWrite()
    {
        if ($this->owner->Identifier == '') {
            $this->owner->Identifier = $this->getGeneratedIdentifier();
        }
    }
    /**
     * @param FieldSet $fields
     */
    public function updateCMSFields(FieldSet &$fields)
    {
        $fields->replaceField(
            'Images',
            new ImageDataObjectManager(
                $this->owner,
                'Images',
                'AdaptiveContentImage',
                'Image'
            )
        );
        $fields->replaceField(
            'LeadImageID',
            new HasOneFileDataObjectManager(
                $this->owner,
                'LeadImage',
                'AdaptiveContentImage',
                'Image'
            )
        );
        if ($this->owner->Identifier == '') {
            $fields->removeByName('Identifier');
            $fields->replaceField(
                'Title',
                new UniqueQueryTextField(
                    array(
                        $this->owner,
                        'getUniqueIdentifierQuery'
                    ),
                    'Title'
                )
            );
        } else {
            $fields->replaceField(
                'Identifier',
                new UniqueQueryTextField(
                    array(
                        $this->owner,
                        'getUniqueIdentifierQuery'
                    ),
                    'Identifier'
                )
            );
        }
    }
    /**
     * @param bool $title
     * @return string
     */
    public function getGeneratedIdentifier($title = false)
    {
        $title = $title ? $title : $this->owner->Title;
        $t = (function_exists('mb_strtolower')) ? mb_strtolower($title) : strtolower($title);
        $t = Object::create('Transliterator')->toASCII($t);
        $t = str_replace('&amp;','-and-',$t);
        $t = str_replace('&','-and-',$t);
        $t = ereg_replace('[^A-Za-z0-9]+','-',$t);
        $t = ereg_replace('-+','-',$t);
        $t = trim($t, '-');
        return $t;
    }
    /**
     * @param bool|string $identifier
     * @return SQLQuery
     */
    public function getUniqueIdentifierQuery($identifier = false)
    {
        if (!$identifier) {
            $identifier = $this->getGeneratedIdentifier();
        }
        return new SQLQuery(
            'COUNT(ID)',
            $this->owner->ClassName,
            "`ID` != '{$this->owner->ID}' AND `ParentID` = '{$this->owner->ParentID}' AND `Identifier` = '$identifier'"
        );
    }
    /**
     * @param $fields
     */
    protected function addValidationFields($fields)
    {
        if ($this->owner->Identifier == '') {
            $fields->removeByName('Identifier');
            $that = $this;
            $fields->replaceField(
                'Title',
                new UniqueQueryTextField(
                    function ($title) use ($that) {
                        return $that->getUniqueIdentifierQuery($that->getGeneratedIdentifier($title));
                    },
                    'Title'
                )
            );
        } else {
            $that = $this;
            $fields->replaceField(
                'Identifier',
                new UniqueQueryTextField(
                    function ($identifier) use ($that) {
                        return $that->getUniqueIdentifierQuery($identifier);
                    },
                    'Identifier'
                )
            );
        }
    }
}