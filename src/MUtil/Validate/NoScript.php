<?php

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * Validate that the input is not an attempt to put any XSS text in the input.
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.0
 */
class MUtil_Validate_NoScript extends \MUtil_Validate_Regexclude
{
    const SCRIPT_REGEX = '/[<>{}\(\)]/';

    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected $_pattern = self::SCRIPT_REGEX;

    /**
     * Sets validator options
     *
     * @param  string|\Zend_Config $pattern
     * @return void
     */
    public function __construct($pattern = self::SCRIPT_REGEX)
    {
        parent::__construct($pattern);

        $this->_messageTemplates[parent::MATCH] = "Html tags may not be entered here.";
    }
}