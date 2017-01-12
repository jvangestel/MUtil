<?php

/**
 *
 * @package    MUtil
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2016, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace MUtil\Snippets;

/**
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2016, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 * @since      Class available since version 1.8.2 Jan 12, 2017 10:59:54 AM
 */
abstract class MultiRowModelFormAbstract extends \MUtil_Snippets_ModelFormSnippetAbstract
{
    /**
     *
     * @var \MUtil_Form_Element_Table
     */
    protected $formTableElement;

    /**
     *
     * @var string
     */
    protected $editTableClass;

    /**
     * Creates from the model a \Zend_Form using createForm and adds elements
     * using addFormElements().
     *
     * @return \Zend_Form
     */
    protected function getModelForm()
    {
        $model     = $this->getModel();
        $baseform  = $this->createForm();

        $bridge    = $model->getBridgeFor('form', new \Gems_Form_SubForm());
        $newData   = $this->addFormElements($bridge, $model);

        $this->formTableElement = new \MUtil_Form_Element_Table(
                $bridge->getForm(),
                $model->getName(),
                array('class' => $this->editTableClass)
                );

        $baseform->setMethod('post')
            ->addElement($this->formTableElement);

        return $baseform;
    }

    /**
     * Hook that loads the form data from $_POST or the model
     *
     * Or from whatever other source you specify here.
     */
    protected function loadFormData()
    {
        $model = $this->getModel();
        $mname = $model->getName();

        // \MUtil_Echo::track($model->getFilter());

        if ($this->request->isPost()) {
            $formData = $this->request->getPost();

            foreach ($formData[$mname] as $id => $row) {
                if (isset($this->formData[$mname], $this->formData[$mname][$id])) {
                    $row = $row + $this->formData[$mname][$id];
                }
                $this->formData[$mname][$id] = $model->loadPostData($row, $this->createData);
            }
            unset($formData[$mname]);
            $this->formData = $this->formData + $formData; // Add post, etc..

        } else {
            // Assume that if formData is set it is the correct formData
            if (! $this->formData)  {
                if ($this->createData) {
                    $this->formData[$mname] = $model->loadNew(2);
                } else {
                    $this->formData[$mname] = $model->load();

                    if (! $this->formData) {
                        throw new \Zend_Exception($this->_('Unknown edit data requested'));
                    }
                }
            }
        }

        // \MUtil_Echo::track($this->formData);
    }

    /**
     * Hook containing the actual save code.
     *
     * Calls afterSave() for user interaction.
     *
     * @see afterSave()
     */
    protected function saveData()
    {
        $this->beforeSave();

        if ($this->csrfId && $this->_csrf) {
            unset($this->formData[$this->csrfId]);
        }

        // Perform the save
        $model = $this->getModel();
        $mname = $model->getName();

        // \MUtil_Echo::track($this->formData[$mname]);
        $this->formData[$mname] = $model->saveAll($this->formData[$mname]);

        $changed = $model->getChanged();

        // Message the save
        $this->afterSave($changed);
    }
}
