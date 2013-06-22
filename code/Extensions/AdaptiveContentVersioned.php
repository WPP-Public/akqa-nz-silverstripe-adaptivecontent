<?php

/**
 * Class AdaptiveContentVersioned
 */
class AdaptiveContentVersioned extends Versioned
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct(
            array(
                'Stage',
                'Live'
            )
        );
    }
    /**
     * @param  null $class
     * @return array
     */
    public static function get_extra_config($class, $extension, $args)
    {
        return array(
            'db'                => array(
                'Version' => 'Int',
            ),
            'has_many'          => array(
                'Versions' => $class
            ),
            'searchable_fields' => array()
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
                'isModifiedNice'  => 'Modified',
                'isPublishedNice' => 'Published'
            )
        );
    }
    /**
     * @param $fields
     */
    public function updateSearchableFields(&$fields)
    {
        unset($fields['isModifiedNice']);
        unset($fields['isPublishedNice']);
    }
    /**
     * @return bool
     */
    public function isNew()
    {
        $id = $this->owner->ID;
        if (empty($id)) {
            return true;
        }
        if (is_numeric($id)) {
            return false;
        }
    }
    /**
     * @return bool
     */
    public function isPublished()
    {
        if ($this->isNew()) {
            return false;
        }

        $table = $this->owner->class;
        while (($p = get_parent_class($table)) !== 'DataObject') {
            $table = $p;
        }

        return (bool) DB::query("SELECT \"ID\" FROM \"{$table}_Live\" WHERE \"ID\" = {$this->owner->ID}")->value();
    }
    /**
     * @param $value
     * @return string
     */
    protected function getBooleanNice($value)
    {
        return $value ? 'Yes' : 'No';
    }
    /**
     * @return mixed
     */
    public function isPublishedNice()
    {
        return $this->getBooleanNice($this->isPublished());
    }
    /**
     * @return mixed
     */
    public function isModifiedNice()
    {
        return $this->getBooleanNice($this->stagesDiffer('Stage', 'Live'));
    }
    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->exists() && $this->stagesDiffer('Stage', 'Live')) {
            $fields->addFieldToTab(
                'Root.Main',
                new LiteralField(
                    'Message',
                    '<div class="message">This item contains unpublished changes</div>'
                ),
                'Identifier'
            );
        }
        $fields->removeByName('Version');
        $fields->removeByName('Versions');
    }
    /**
     * @param $fromStage
     * @param $toStage
     */
    public function publishChildren($fromStage, $toStage)
    {
        if ($this->owner->hasExtension('AdaptiveContentHierarchy')) {
            $this->publish($fromStage, $toStage);
            $children = $this->owner->Children();
            if ($children instanceof DataList) {
                foreach ($children as $child) {
                    $child->publishChildren($fromStage, $toStage);
                }
            }
        }
    }
}
