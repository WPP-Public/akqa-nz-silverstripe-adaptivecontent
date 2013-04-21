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
     * @param $fields
     */
    public function updateSummaryFields(&$fields)
    {
        $fields = array_merge(
            $fields,
            array(
                'isModifiedNice' => 'Modified',
                'isPublishedNice' => 'Published'
            )
        );
    }
    /**
     * @return bool
     */
    public function isPublished()
    {
        return (bool) DB::query("SELECT \"ID\" FROM \"{$this->owner->ClassName}_Live\" WHERE \"ID\" = $this->owner->ID")->value();
    }
    /**
     * @return bool
     */
    public function isModified()
    {
        $classname = $this->owner->ClassName;
        $id = $this->owner->ID;
        $stageVersion = Versioned::get_versionnumber_by_stage(
            $classname,
            'Stage',
            $id
        );
        $liveVersion = Versioned::get_versionnumber_by_stage(
            $classname,
            'Live',
            $id
        );
        return ($stageVersion && $stageVersion != $liveVersion);
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
        return $this->getBooleanNice($this->isModified());
    }
    /**
     * @param FieldSet $fields
     */
    public function updateCMSFields(FieldSet &$fields)
    {
        if ($this->owner->exists() && $this->isModified()) {
            $fields->addFieldToTab(
                'Root.Main',
                new LiteralField(
                    'Message',
                    '<div class="message">This item contains unpublished changes</div>'
                ),
                'SortOrder' //TODO: This is a dependency
            );
        }
        $fields->removeByName('Version');
        $fields->removeByName('Versions');
    }
    /**
     * @param FieldSet $actions
     */
    public function updateCMSActions(FieldSet &$actions)
    {
        $actions->push(
            new FormAction(
                'doPublish',
                'Publish'
            )
        );
        if ($this->owner->hasExtension('AdaptiveContentHierarchy')) {
            $actions->push(
                new FormAction(
                    'doPublishChildren',
                    'Publish Children'
                )
            );
        }
    }
    /**
     * @param $fromStage
     * @param $toStage
     */
    public function publishChildren($fromStage, $toStage)
    {
        if ($this->owner->hasExtension('AdaptiveContentHierarchy')) {
            $this->owner->publish($fromStage, $toStage);
            $children = $this->owner->Children();
            if ($children instanceof DataObjectSet) {
                foreach ($children as $child) {
                    $child->publishChildren($fromStage, $toStage);
                }
            }
        }
    }
}