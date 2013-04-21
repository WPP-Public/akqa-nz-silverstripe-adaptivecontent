<?php

class AdaptiveContentHierarchy extends DataObjectDecorator
{
    /**
     * @var
     */
    protected $relation;
    /**
     * @var string
     */
    public static $default_sort = '`HierarchySortOrder` ASC';
    /**
     * @param $relation
     */
    public function __construct($relation)
    {
        $this->relation = $relation;
    }
    /**
     * @return array
     */
    public function extraStatics()
    {
        return array(
            'db' => array(
                'HierarchySortOrder' => 'Varchar(255)',
                'HierarchyTitle' => 'Varchar(255)',
                'SortOrder' => 'Int'
            ),
            'has_one' => array(
                'Parent' => $this->relation,
            ),
            'has_many' => array(
                'Children' => $this->relation
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
        $fields->replaceField(
            'Children',
            new DataObjectManager(
                $this->owner,
                'Children',
                $this->relation,
                array(
                    'Title' => 'Title' // TODO: This is a dependency
                ),
                'getCMSFields',
                null,
                'SortOrder'
            )
        );
        $fields->removeByName('HierarchySortOrder');
        $fields->removeByName('HierarchyTitle');
        $fields->removeByName('Parent');
    }
    /**
     * @return string
     */
    protected function getSortOrderChar()
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