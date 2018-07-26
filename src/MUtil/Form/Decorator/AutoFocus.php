<?php

/**
 *
 * @package    MUtil
 * @subpackage Form
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

use MUtil\Bootstrap\Form\Element\Hidden as BootstrapHidden;
use MUtil\Form\Element\Hidden as BaseHidden;


/**
 * Form decorator that sets the focus on the first suitable element.
 *
 * @package    MUtil
 * @subpackage Form
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class MUtil_Form_Decorator_AutoFocus extends \Zend_Form_Decorator_Abstract
{
    /**
     *
     * @var \MUtil\Form\Element\Hidden
     */
    private $_focusTrackerElement;

    /**
     *
     * @param type $element
     * @return type
     */
    private function _getFocus($element)
    {
        // \MUtil_Echo::r(get_class($element));
        if ($element instanceof \MUtil_Form_Element_SubFocusInterface) {
            foreach ($element->getSubFocusElements() as $subelement) {
                if ($focus = $this->_getFocus($subelement)) {
                    return $focus;
                }
            }
        } elseif ($element instanceof \Zend_Form_Element) {
            if (($element instanceof \Zend_Form_Element_Hidden) ||
                ($element instanceof \MUtil_Form_Element_NoFocusInterface) ||
                ($element->getAttrib('readonly')) ||
                ($element->helper == 'Button') ||
                ($element->helper == 'formSubmit') ||
                ($element->helper == 'SubmitButton')) {
                return null;
            }
            return $element->getId();

        } elseif (($element instanceof \Zend_Form) ||
                  ($element instanceof \Zend_Form_DisplayGroup)) {
            foreach ($element as $subelement) {
                if ($focus = $this->_getFocus($subelement)) {
                    return $focus;
                }
            }
        }

        return null;
    }

    /**
     * Render form elements
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $form  = $this->getElement();
        $view  = $form->getView();
        $request = \Zend_Controller_Front::getInstance()->getRequest();
        // \MUtil_Echo::track($this->_focusTrackerElementId, $form->getValue($this->_focusTrackerElementId), $request->getParam($this->_focusTrackerElementId));

        $focus = $request->getParam($form->focusTrackerElementId) ? $request->getParam($form->focusTrackerElementId) : $this->_getFocus($form);

        if ($form->focusTrackerElementId) {
            $form->getElement($form->focusTrackerElementId)->setValue($focus);
        }

        if (($view !== null) && ($focus !== null)) {
            // Use try {} around e.select as nog all elements have a select() function
            $script = "e = document.getElementById('$focus');";
            $script .= "
                if (e) {
                    e.focus();
                    try {
                        if (e.select) {
                            e.select();
                        }
                    } catch (ex) {}
                }";
            
            $view->inlineScript()->appendScript($script);
        }

        return $content;
    }
}
