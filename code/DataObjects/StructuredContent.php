<?php
//
///**
// * Class StructuredContent
// */
//class StructuredContent extends DataObject
//{
//    /**
//     * @var string
//     */
//    public static $default_sort = '`HierarchySortOrder` ASC';
//    /**
//     * @var array
//     */
//    public static $extensions = array(
//        "Versioned('Stage', 'Live')",
//    );
//    /**
//     * @var array
//     */
//    public static $db = array(
//        'HierarchySortOrder' => 'Varchar(255)',
//        'HierarchyTitle' => 'Varchar(255)',
//        'SortOrder' => 'Int',
//        'Identifier' => 'Varchar(255)',
//        'Title' => 'Varchar(255)',
//        'SecondaryIdentifier' => 'Varchar(255)',
//        'SubTitle' => 'Varchar(255)',
//        'Teaser' => 'Text',
//        'ShortTeaser' => 'Text',
//        'HTML' => 'HTMLText'
//    );
//    /**
//     * @var array
//     */
//    public static $has_one = array(
//        'Parent' => 'StructuredContent',
//        'LeadImage' => 'StructuredContentImage'
//    );
//    /**
//     * @var array
//     */
//    public static $has_many = array(
//        'Images' => 'StructuredContentImage',
//        'Children' => 'StructuredContent'
//    );
//    /**
//     * @var array
//     */
//    public static $summary_fields = array(
//        'HierarchyTitle' => 'Title',
//        'isModifiedNice' => 'Modified',
//        'isPublishedNice' => 'Published'
//    );
//    public static $searchable_fields = array(
//        'Title' => 'Title'
//    );
//    /**
//     * @var array
//     */
//    public static $casting = array(
//        'isModified' => 'Boolean',
//        'isPublished' => 'Boolean'
//    );
//    /**
//     * @param $value
//     * @return string
//     */
//    protected function getBooleanNice($value)
//    {
//        return $value ? 'Yes' : 'No';
//    }
//    /**
//     * @return bool
//     */
//    public function isPublished()
//    {
//        return (bool) DB::query("SELECT \"ID\" FROM \"{$this->ClassName}_Live\" WHERE \"ID\" = $this->ID")->value();
//    }
//    /**
//     * @return mixed
//     */
//    public function isPublishedNice()
//    {
//        return $this->getBooleanNice($this->isPublished());
//    }
//    /**
//     * @return bool
//     */
//    public function isModified()
//    {
//        $stageVersion = Versioned::get_versionnumber_by_stage($this->ClassName, 'Stage', $this->ID);
//        $liveVersion =	Versioned::get_versionnumber_by_stage($this->ClassName, 'Live', $this->ID);
//        return ($stageVersion && $stageVersion != $liveVersion);
//    }
//    /**
//     * @return mixed
//     */
//    public function isModifiedNice()
//    {
//        return $this->getBooleanNice($this->isModified());
//    }
//    /**
//     *
//     */
//    protected function onBeforeWrite()
//    {
//        if ($this->SortOrder === null && (array_key_exists('ParentID', $this->record) || $this->exists())) {
//            $this->SortOrder = $this->getNextSortOrder();
//        }
//        if ($this->Identifier == '') {
//            $this->Identifier = $this->getGeneratedIdentifier();
//        }
//        $parent = $this;
//        $sorts = array(
//            $parent->getSortOrderChar()
//        );
//        $titles = array(
//            $parent->Title
//        );
//        while ($parent->ParentID) {
//            $parent = $parent->Parent();
//            $sorts[] = $parent->getSortOrderChar();
//            $titles[] = $parent->Title;
//        }
//        $this->HierarchySortOrder = implode('', array_reverse($sorts));
//        $this->HierarchyTitle = implode(' -> ', array_reverse($titles));
//
//        parent::onBeforeWrite();
//
//        $children = $this->Children();
//        if ($children instanceof IteratorAggregate) {
//            foreach ($children as $child) {
//                $child->write();
//            }
//        }
//    }
//    /**
//     * @return string
//     */
//    protected function getSortOrderChar()
//    {
//        $sortOrder = $this->SortOrder + 33;
//        $num = $sortOrder / 126;
//        if ($num > 1) {
//            return str_repeat(chr(126), floor($num)) . chr($sortOrder % 126);
//        } else {
//            return chr($sortOrder);
//        }
//    }
//    /**
//     * @return SortredFields
//     */
//    public function getCMSValidator()
//    {
//        return new RequiredFields('Identifier', 'Title');
//    }
//    /**
//     * @param null $params
//     * @return FieldSet
//     */
//    public function getCMSFields($params = null)
//    {
//        $fields = parent::getCMSFields($params);
//        if ($this->exists() && $this->isModified()) {
//            $fields->addFieldToTab('Root.Main', new LiteralField('Message', '<div class="message">This item contains unpublished changes</div>'), 'SortOrder');
//        }
//        $this->removeUneededFields($fields);
//        $this->addValidationFields($fields);
//        $fields->replaceField(
//            'Children',
//            new DataObjectManager(
//                $this,
//                'Children',
//                'StructuredContent',
//                array(
//                    'Title' => 'Title'
//                ),
//                'getSubCMSFields',
//                null,
//                'SortOrder'
//            )
//        );
//        $fields->replaceField(
//            'Images',
//            new ImageDataObjectManager(
//                $this,
//                'Images',
//                'StructuredContentImage',
//                'Image'
//            )
//        );
//        $fields->replaceField(
//            'LeadImageID',
//            new HasOneFileDataObjectManager(
//                $this,
//                'LeadImage',
//                'StructuredContentImage',
//                'Image'
//            )
//        );
//        return $fields;
//    }
//    /**
//     * @param $fields
//     */
//    protected function addValidationFields($fields)
//    {
//        if ($this->Identifier == '') {
//            $fields->removeByName('SortOrder');
//            $fields->removeByName('Identifier');
//            $that = $this;
//            $fields->replaceField(
//                'Title',
//                new UniqueQueryTextField(
//                    function ($title) use ($that) {
//                        return $that->getUniqueIdentifierQuery($that->getGeneratedIdentifier($title));
//                    },
//                    'Title'
//                )
//            );
//        } else {
//            $that = $this;
//            $fields->replaceField(
//                'Identifier',
//                new UniqueQueryTextField(
//                    function ($identifier) use ($that) {
//                        return $that->getUniqueIdentifierQuery($identifier);
//                    },
//                    'Identifier'
//                )
//            );
//        }
//    }
//    /**
//     * @return FieldSet
//     */
//    public function getSubCMSFields()
//    {
//        $fields = parent::getCMSFields();
//        $this->removeUneededFields($fields);
//        $this->addValidationFields($fields);
//        $fields->removeByName('Images');
//        $fields->removeByName('Children');
//        $fields->replaceField(
//            'LeadImageID',
//            new HasOneFileDataObjectManager(
//                $this,
//                'LeadImage',
//                'StructuredContentImage',
//                'Image'
//            )
//        );
//        $fields->replaceField('HTML', new SimpleHTMLEditorField('HTML', 'Html'));
//        return $fields;
//    }
//    /**
//     * @param FieldSet $fields
//     */
//    protected function removeUneededFields(FieldSet $fields)
//    {
//        $fields->removeByName('HierarchySortOrder');
//        $fields->removeByName('HierarchyTitle');
//        $fields->removeByName('Version');
//        $fields->removeByName('Versions');
//        $fields->removeByName('Parent');
//    }
//    /**
//     * @return FieldSet
//     */
//    public function getCMSActions()
//    {
//        $fields = parent::getCMSActions();
//        $fields->push(new FormAction('doPublish', 'Publish'));
//        $fields->push(new FormAction('doPublishChildren', 'Publish Children'));
//        return $fields;
//    }
//    /**
//     * @param bool $title
//     * @return string
//     */
//    public function getGeneratedIdentifier($title = false)
//    {
//        $title = $title ? $title : $this->Title;
//        $t = (function_exists('mb_strtolower')) ? mb_strtolower($title) : strtolower($title);
//        $t = Object::create('Transliterator')->toASCII($t);
//        $t = str_replace('&amp;','-and-',$t);
//        $t = str_replace('&','-and-',$t);
//        $t = ereg_replace('[^A-Za-z0-9]+','-',$t);
//        $t = ereg_replace('-+','-',$t);
//        $t = trim($t, '-');
//        return $t;
//    }
//    /**
//     * @param bool|string $identifier
//     * @return SQLQuery
//     */
//    public function getUniqueIdentifierQuery($identifier = false)
//    {
//        if (!$identifier) {
//            $identifier = $this->getGeneratedIdentifier();
//        }
//        return new SQLQuery(
//            'COUNT(ID)',
//            $this->ClassName,
//            "`ID` != '$this->ID' AND `ParentID` = '{$this->ParentID}' AND `Identifier` = '$identifier'"
//        );
//    }
//    /**
//     * @return mixed
//     */
//    protected function getSiblings()
//    {
//        return DataObject::get($this->ClassName, "ParentID = '$this->ParentID'", 'SortOrder ASC');
//    }
//    /**
//     * @return int|mixed
//     */
//    protected function getNextSortOrder()
//    {
//        $siblings = $this->getSiblings();
//        if ($siblings instanceof DataObjectSet) {
//            return $siblings->Last()->SortOrder + 1;
//        } else {
//            return 0;
//        }
//    }
//    /**
//     * @param $fromStage
//     * @param $toStage
//     */
//    public function publishChildren($fromStage, $toStage)
//    {
//        $this->publish($fromStage, $toStage);
//        $children = $this->Children();
//        if ($children instanceof DataObjectSet) {
//            foreach ($children as $child) {
//                $child->publishChildren($fromStage, $toStage);
//            }
//        }
//    }
//}