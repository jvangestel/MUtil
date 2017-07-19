<?php

/**
 *
 * @package    MUtil
 * @subpackage Form_Element
 * @author     Menno Dekker <menno.dekker@erasmusmc.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

/**
 * Show a table containing a subform repeated for the number of rows set for
 * this item when rendered.
 *
 * @package    MUtil
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.6.5
 */
class MUtil_Bootstrap_Form_Element_MultiCheckbox extends \Zend_Form_Element_MultiCheckbox
{

    /**
     * Constructor
     *
     * $spec may be:
     * - string: name of element
     * - array: options with which to configure element
     * - \Zend_Config: \Zend_Config with options for configuring element
     *
     * @param  string|array|\Zend_Config $spec
     * @param  array|\Zend_Config $options
     * @return void
     * @throws \Zend_Form_Exception if no element name after initialization
     */
    public function __construct($spec, $options = null)
    {
        parent::__construct($spec, $options);
        $this->setAttrib('label_class', 'checkbox');
    }

	/**
     * Load default decorators
     *
     * @return \Zend_Form_Element
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('ViewHelper')
                 ->addDecorator('Errors')
                 ->addDecorator('Description', array('tag' => 'p', 'class' => 'help-block'))
                 ->addDecorator('HtmlTag', array(
                     'tag' => 'div',
                     'id'  => array('callback' => array(get_class($this), 'resolveElementId')),
                     'class' => 'element-container'
                 ))
                 ->addDecorator('Label')
                 ->addDecorator('BootstrapRow');
        }

        if (false !== $decorator = $this->getDecorator('label')) {
            $decorator->setOption('disableFor', true);
        }

        return $this;
    }
}