<?php

class AdaptiveContentHierarchy extends DataObjectDecorator
{
    /**
     * @var bool
     */
    protected $skipChildrenField = false;
    /**
     * @param boolean $skipChildrenField
     */
    public function setSkipChildrenField($skipChildrenField)
    {
        $this->skipChildrenField = $skipChildrenField;
    }
    /**
     * @param null $class
     * @return array
     */
    public function extraStatics($class = null)
    {
        return array(
            'db' => array(
                'HierarchySortOrder' => 'Varchar(255)',
                'HierarchyTitle' => 'Varchar(255)',
                'SortOrder' => 'Int'
            ),
            'has_one' => array(
                'Parent' => ($class) ? $class : $this->owner->class,
            ),
            'has_many' => array(
                'Children' => ($class) ? $class : $this->owner->class
            )
        );
    }
    /**
     * @param $fields
     */
    public function updateSummaryFields(&$fields)
    {
        $fields = array_merge(
            $fields,
            array(
                'HierarchyTitle' => 'Title'
            )
        );
    }
    /**
     *
     */
    public function onBeforeWrite()
    {
        if ($this->owner->SortOrder === null && (array_key_exists('ParentID', $this->owner->toMap()) || $this->owner->exists())) {
            $this->owner->SortOrder = $this->getNextSortOrder();
        }
        $parent = $this->owner;
        $sorts = array(
            $parent->getSortOrderChar()
        );
        $titles = array(
            $parent->Title
        );
        while ($parent->ParentID) {
            $parent = $parent->Parent();
            $sorts[] = $parent->getSortOrderChar();
            $titles[] = $parent->Title;
        }
        $this->owner->HierarchySortOrder = implode('', array_reverse($sorts));
        $this->owner->HierarchyTitle = implode(' -> ', array_reverse($titles));

        $children = $this->owner->Children();
        if ($children instanceof IteratorAggregate) {
            foreach ($children as $child) {
                $child->write();
            }
        }
    }
    /**
     * @param FieldSet $fields
     */
    public function updateCMSFields(FieldSet &$fields)
    {
        if (!$this->skipChildrenField) {
            $fields->replaceField(
                'Children',
                new DataObjectManager(
                    $this->owner,
                    'Children',
                    $this->owner->class,
                    array(
                        'Title' => 'Title' // TODO: This is a dependency
                    ),
                    'getLimitedCMSFields',
                    null,
                    'SortOrder'
                )
            );
        }
        $fields->removeByName('HierarchySortOrder');
        $fields->removeByName('HierarchyTitle');
        $fields->removeByName('Parent');
        $fields->insertBefore(
            $fields->dataFieldByName('SortOrder'),
            'Identifier'
        );
    }
    /**
     * @return FieldSet
     */
    public function getLimitedCMSFields()
    {
        $this->setSkipChildrenField(true);
        $fields = $this->owner->getCMSFields();
        // TODO: This is a dependency
        $fields->replaceField(
            'HTML',
            new SimpleHTMLEditorField(
                'HTML',
                'Html'
            )
        );
        return $fields;
    }
    /**
     * @return string
     */
    public function getSortOrderChar()
    {
        $sortOrder = $this->owner->SortOrder + 33;
        $num = $sortOrder / 126;
        if ($num > 1) {
            return str_repeat(chr(126), floor($num)) . chr($sortOrder % 126);
        } else {
            return chr($sortOrder);
        }
    }
    /**
     * @return int|mixed
     */
    protected function getNextSortOrder()
    {
        $siblings = $this->getSiblings();
        if ($siblings instanceof DataObjectSet) {
            return $siblings->Last()->SortOrder + 1;
        } else {
            return 0;
        }
    }
    /**
     * @return mixed
     */
    protected function getSiblings()
    {
        return DataObject::get(
            $this->owner->ClassName,
            "ParentID = '{$this->owner->ParentID}'",
            'SortOrder ASC'
        );
    }
}