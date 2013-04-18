<?php

/**
 * Class StructuredContent
 */
class StructuredContent extends DataObject
{
    /**
     * @var string
     */
    public static $default_sort = 'HierarchySortOrder ASC';
    /**
     * @var array
     */
    public static $extensions = array(
        "Versioned('Stage', 'Live')",
    );
    /**
     * @var array
     */
    public static $db = array(
        'HierarchySortOrder' => 'Float',
        'Identifier' => 'Varchar(255)',
        'SecondaryIdentifier' => 'Varchar(255)',
        'Title' => 'Varchar(255)',
        'SubTitle' => 'Varchar(255)',
        'Teaser' => 'Text',
        'ShortTeaser' => 'Text',
        'HTML' => 'HTMLText'
    );
    /**
     * @var array
     */
    public static $has_one = array(
        'Parent' => 'StructuredContent',
        'LeadImage' => 'StructuredContentImage'
    );
    /**
     * @var array
     */
    public static $has_many = array(
        'Images' => 'StructuredContentImage',
        'Children' => 'StructuredContent'
    );
    protected function onBeforeWrite()
    {
        $parent = $this;
        $sorts = array(
            $parent->SortOrder
        );
        while ($parent->ParentID) {
            $parent = $parent->Parent();
            $sorts[] = $parent->SortOrder;
        }
        $this->HierarchySortOrder = (float) implode('.', array_reverse($sorts));
        parent::onBeforeWrite();
    }
    /**
     * @param null $params
     * @return FieldSet
     */
    public function getCMSFields($params = null)
    {
        $fields = parent::getCMSFields($params);
        $this->removeUneededFields($fields, true);
        $fields->replaceField(
            'Children',
            new DataObjectManager(
                $this,
                'Children',
                'StructuredContent',
                array(
                    'Title' => 'Title'
                ),
                'getSubCMSFields'
            )
        );
        $fields->replaceField(
            'Images',
            new ImageDataObjectManager(
                $this,
                'Images',
                'StructuredContentImage',
                'Image'
            )
        );
        $fields->replaceField(
            'LeadImageID',
            new HasOneFileDataObjectManager(
                $this,
                'LeadImage',
                'StructuredContentImage',
                'Image'
            )
        );
        return $fields;
    }
    /**
     * @return FieldSet
     */
    public function getSubCMSFields()
    {
        $fields = parent::getCMSFields();
        $this->removeUneededFields($fields);
        $fields->removeByName('Images');
        $fields->removeByName('Children');
        $fields->replaceField(
            'LeadImageID',
            new HasOneFileDataObjectManager(
                $this,
                'LeadImage',
                'StructuredContentImage',
                'Image'
            )
        );
        $fields->replaceField('HTML', new SimpleHTMLEditorField('HTML', 'Html'));
        return $fields;
    }
    /**
     * @param FieldSet $fields
     * @param bool     $first
     */
    protected function removeUneededFields(FieldSet $fields, $first = false)
    {
        $fields->removeByName('HierarchyTitle');
        $fields->removeByName('Version');
        $fields->removeByName('Versions');
        $fields->removeByName('Parent');
        if (!$first) {
            $fields->removeByName('SortOrder');
        }
    }
    /**
     * @return FieldSet
     */
    public function getCMSActions()
    {
        $fields = parent::getCMSActions();
        $fields->push(new FormAction('doPublish', 'Publish'));
        return $fields;
    }

}