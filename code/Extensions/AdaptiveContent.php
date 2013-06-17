<?php

/**
 * Class AdaptiveContent
 */
class AdaptiveContent extends DataExtension
{
    /**
     * @var array
     */
    private static $db = array(
        'Identifier' => 'Varchar(255)',
        'Title' => 'Varchar(255)',
        'SecondaryIdentifier' => 'Varchar(255)',
        'SubTitle' => 'Varchar(255)',
        'Teaser' => 'Text',
        'ShortTeaser' => 'Text',
        'Content' => 'HTMLText'
    );
    /**
     * @var array
     */
    private static $has_one = array(
        'LeadImage' => 'AdaptiveContentImage'
    );
    /**
     * @var array
     */
    private static $has_many = array(
        'Images' => 'AdaptiveContentImage'
    );
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
    public function updateCMSFields(FieldList $fields)
    {
//        $fields->replaceField(
//            'Images',
//            new ImageDataObjectManager(
//                $this->owner,
//                'Images',
//                'AdaptiveContentImage',
//                'Image'
//            )
//        );
//        $fields->replaceField(
//            'LeadImageID',
//            new HasOneFileDataObjectManager(
//                $this->owner,
//                'LeadImage',
//                'AdaptiveContentImage',
//                'Image'
//            )
//        );
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
     * @param  bool   $title
     * @return string
     */
    public function getGeneratedIdentifier($title = false)
    {
        return URLSegmentFilter::create()->filter($title ? $title : $this->owner->Title);
    }
    /**
     * @param  bool|string $identifier
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
