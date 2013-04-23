<?php

/**
 * Class AdaptiveContentModelAdmin
 */
class AdaptiveContentModelAdmin extends ModelAdmin
{
    /**
     * @var array
     */
    public static $managed_models = array();
    /**
     * @var string
     */
    public static $url_segment = 'adaptive-content';
    /**
     * @var string
     */
    public static $menu_title = 'Adaptive Content';
    /**
     * @var string
     */
    public static $record_controller_class = 'AdaptiveContentModelAdmin_RecordController';
    /**
     * @var string
     */
    protected $resultsTableClassName = 'AdaptiveContentModelAdmin_TableListField';
}

/**
 * Class AdaptiveContentModelAdmin_RecordController
 */
class AdaptiveContentModelAdmin_RecordController extends ModelAdmin_RecordController
{
    /**
     *
     */
    public function init()
    {
        if ($this->currentRecord->hasExtension('AdaptiveContentHierarchy')) {
            Requirements::javascript(
                'silverstripe-adaptivecontent/resources/model-admin-adaptive-content-versioned-hierarchy.js'
            );
        }
        if ($this->currentRecord->hasExtension('AdaptiveContentVersioned')) {
            Requirements::javascript(
                'silverstripe-adaptivecontent/resources/model-admin-adaptive-content-versioned.js'
            );
        }
        parent::init();
    }
    /**
     * @param  array                  $data
     * @param  Form                   $form
     * @param  SS_HTTPRequest         $request
     * @return SS_HTTPResponse|string
     */
    public function doPublish($data, $form, $request)
    {
        $form->saveInto($this->currentRecord);

        try {
            $this->currentRecord->write();
            $this->currentRecord->publish('Stage', 'Live');
        } catch (ValidationException $e) {
            $form->sessionMessage($e->getResult()->message(), 'bad');
        }

        // Behaviour switched on ajax.
        if (Director::is_ajax()) {
            return $this->edit($request);
        } else {
            Director::redirectBack();
        }
    }
    /**
     * @param $data
     * @param $form
     * @param $request
     * @return SS_HTTPResponse|string
     */
    public function doPublishChildren($data, $form, $request)
    {
        $form->saveInto($this->currentRecord);

        try {
            $this->currentRecord->publishChildren('Stage', 'Live');
        } catch (ValidationException $e) {
            $form->sessionMessage($e->getResult()->message(), 'bad');
        }

        // Behaviour switched on ajax.
        if (Director::is_ajax()) {
            return $this->edit($request);
        } else {
            Director::redirectBack();
        }
    }
}

/**
 * Class AdaptiveContentModelAdmin_TableListField
 */
class AdaptiveContentModelAdmin_TableListField extends TableListField
{
    /**
     * @param SQLQuery $query
     */
    public function setCustomQuery(SQLQuery $query)
    {
        if (singleton($this->sourceClass)->hasExtension('AdaptiveContentHierarchy')) {
            $query->orderby('"HierarchySortOrder" ASC');
        }
        parent::setCustomQuery($query);
    }
}
