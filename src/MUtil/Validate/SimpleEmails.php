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
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.0
 */
class MUtil_Validate_SimpleEmails extends \Zend_Validate_Regex
{
    // Reg checked at Wikipedia, only | and ` chars are technically allowed in name and not in there fro security reasons.
    const EMAILS_REGEX = '/^(([[:alnum:]._!#$%*\\/&?{}+=\'^~-])+@[[:alnum:]]+[[:alnum:].-]+\\.[[:alpha:]]{2,})([\s,;]+([[:alnum:]._!#$%*\\/&?{}+=\'^~-])+@[[:alnum:]]+[[:alnum:].-]+\\.[[:alpha:]]{2,})*$/';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID   => "Invalid type given, value should be string, integer or float",
        self::NOT_MATCH => "'%value%' is not a series of email addresses (e.g. name@somewhere.com, nobody@nowhere.org).",
    );

    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected $_pattern = self::EMAILS_REGEX;

    /**
     * Sets validator options
     *
     * @param  string|\Zend_Config $pattern
     * @return void
     */
    public function __construct($pattern = self::EMAILS_REGEX)
    {
        parent::__construct($pattern);
    }
}