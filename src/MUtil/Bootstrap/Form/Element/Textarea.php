<?php

/**
 *
 * @package    MUtil
 * @subpackage Form_Element
 * @author     Menno Dekker <menno.dekker@erasmusmc.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Bootstrap\Form\Element;

use MUtil\Form\Element\Textarea as BaseTextarea;

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
class Textarea extends BaseTextarea
{
	/**
	 * Bootstrap class for an input tag. Remove if you want the normal layout.
	 * @var string
	 */
	protected $_elementClass = 'form-control';

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
		$this->addClass($this->_elementClass);
	}

	/**
 	 * Add a class to an existing class, taking care of spacing
 	 * @param string $targetClass  The existing class
 	 * @param string $addClass    the Class or classes to add, seperated by spaces
 	 */
    protected function addClass($addClass)
    {
    	$targetClass = $this->getAttrib('class');
    	if(!empty($targetClass) && (strpos($targetClass, $addClass) === false)) {
    		$targetClass .= " {$addClass}";
       	} else {
       		$targetClass = $addClass;
       	}
       	$this->setAttrib('class', $targetClass);
  		return $this;
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
        return $this;
    }
}