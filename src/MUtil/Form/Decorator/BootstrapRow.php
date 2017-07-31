<?php

/**
 *
 * @package    MUtil
 * @subpackage Form_Decorator
 * @author     Jasper van Gestel <jappie@dse.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * Display a form in a table decorator.
 *
 * @package    MUtil
 * @subpackage Form_Decorator
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */
class MUtil_Form_Decorator_BootstrapRow extends \Zend_Form_Decorator_Abstract
{
    protected $_format = '
    <div class="form-group">
    	%s
    </div>';

    protected $_errorFormat = '
    <div class="form-group has-error has-feedback">
        %s
    </div>';

    protected $_elementClass = 'form-control';

    public function render($content)
    {
        $element = $this->getElement();
        if($element->hasErrors()) {
            $markup  = sprintf($this->_errorFormat, $content);
        } else {
            $markup  = sprintf($this->_format, $content);
        }
        return $markup;
    }
}