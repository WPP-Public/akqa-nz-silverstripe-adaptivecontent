<?php

/**
 * Class AdaptiveContentVersionedDetailsForm
 */
class AdaptiveContentVersionedDetailsForm extends GridFieldDetailForm
{
}

/**
 * Class AdaptiveContentVersionedDetailsForm_ItemRequest
 */
class AdaptiveContentVersionedDetailsForm_ItemRequest extends GridFieldDetailForm_ItemRequest
{
    /**
     * @var array
     */
    private static $allowed_actions = array(
        'edit',
        'view',
        'ItemEditForm'
    );
    /**
     * @return Form
     */
    public function ItemEditForm()
    {
        $form = parent::ItemEditForm();
        /* @var $actions FieldList */
        $actions = $form->Actions();

        $actions->replaceField(
            'action_doSave',
            FormAction::create('save', _t('SiteTree.BUTTONSAVED', 'Saved'))
                ->setAttribute('data-icon', 'accept')
                ->setAttribute('data-icon-alternate', 'addpage')
                ->setAttribute('data-text-alternate', _t('CMSMain.SAVEDRAFT', 'Save draft'))
                ->setUseButtonTag(true)
        );

        /* @var $publish FormAction */
        $publish = FormAction::create('publish', _t('SiteTree.BUTTONPUBLISHED', 'Published'))
            ->setAttribute('data-icon', 'accept')
            ->setAttribute('data-icon-alternate', 'disk')
            ->setAttribute('data-text-alternate', _t('SiteTree.BUTTONSAVEPUBLISH', 'Save & publish'))
            ->setUseButtonTag(true);

        if ($this->record->stagesDiffer('Stage', 'Live')) {
            $publish->addExtraClass('ss-ui-alternate');
        }

        $actions->push($publish);

        if ($this->record->isPublished()) {
            /* @var $unpublish FormAction */
            $unpublish = FormAction::create('unpublish', _t('SiteTree.BUTTONUNPUBLISH', 'Unpublish'), 'delete')
                ->addExtraClass('ss-ui-action-destructive');

            $actions->push($unpublish);
        }


        return $form;
    }

    /**
     * @param $data
     * @param $form
     * @return HTMLText|SS_HTTPResponse|ViewableData_Customised
     */
    public function save($data, $form)
    {
        return $this->doSave($data, $form);
    }

    /**
     * @param $data
     * @param $form
     * @return HTMLText|SS_HTTPResponse|ViewableData_Customised
     */
    public function publish($data, $form)
    {
        $new_record = $this->record->ID == 0;
        $controller = Controller::curr();
        $list = $this->gridField->getList();

        if ($list instanceof ManyManyList) {
            // Data is escaped in ManyManyList->add()
            $extraData = (isset($data['ManyMany'])) ? $data['ManyMany'] : null;
        } else {
            $extraData = null;
        }

        if (!$this->record->canEdit()) {
            return $controller->httpError(403);
        }

        if (isset($data['ClassName']) && $data['ClassName'] != $this->record->ClassName) {
            $newClassName = $data['ClassName'];
            // The records originally saved attribute was overwritten by $form->saveInto($record) before.
            // This is necessary for newClassInstance() to work as expected, and trigger change detection
            // on the ClassName attribute
            $this->record->setClassName($this->record->ClassName);
            // Replace $record with a new instance
            $this->record = $this->record->newClassInstance($newClassName);
        }

        try {
            $form->saveInto($this->record);
            $this->record->write();
            $list->add($this->record, $extraData);
            $this->record->publish('Stage', 'Live');
        } catch (ValidationException $e) {
            $form->sessionMessage($e->getResult()->message(), 'bad');
            $responseNegotiator = new PjaxResponseNegotiator(array(
                'CurrentForm' => function () use (&$form) {
                    return $form->forTemplate();
                },
                'default'     => function () use (&$controller) {
                    return $controller->redirectBack();
                }
            ));
            if ($controller->getRequest()->isAjax()) {
                $controller->getRequest()->addHeader('X-Pjax', 'CurrentForm');
            }

            return $responseNegotiator->respond($controller->getRequest());
        }

        // TODO Save this item into the given relationship

        $link = '<a href="' . $this->Link('edit') . '">"'
            . htmlspecialchars($this->record->Title, ENT_QUOTES)
            . '"</a>';
        $message = sprintf(
            'Published %s %s',
            $this->record->i18n_singular_name(),
            $link
        );

        $form->sessionMessage($message, 'good');

        if ($new_record) {
            return Controller::curr()->redirect($this->Link());
        } elseif ($this->gridField->getList()->byId($this->record->ID)) {
            // Return new view, as we can't do a "virtual redirect" via the CMS Ajax
            // to the same URL (it assumes that its content is already current, and doesn't reload)
            return $this->edit(Controller::curr()->getRequest());
        } else {
            // Changes to the record properties might've excluded the record from
            // a filtered list, so return back to the main view if it can't be found
            $noActionURL = $controller->removeAction($data['url']);
            $controller->getRequest()->addHeader('X-Pjax', 'Content');

            return $controller->redirect($noActionURL, 302);
        }
    }

    /**
     * @return HTMLText|ViewableData_Customised
     */
    public function unPublish()
    {
        $origStage = Versioned::current_stage();
        Versioned::reading_stage('Live');

        // This way our ID won't be unset
        $clone = clone $this->record;
        $clone->delete();

        Versioned::reading_stage($origStage);

        return $this->edit(Controller::curr()->getRequest());
    }
}