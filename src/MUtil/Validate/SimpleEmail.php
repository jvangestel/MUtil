<?php

/**
 *
 * @package     MUtil
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class MUtil_Validate_SimpleEmail extends \Zend_Validate_Regex
{
    // Reg checked at Wikipedia, only | and ` chars are technically allowed in name and not in there fro security reasons.
    const EMAIL_REGEX = '/^(([[:alnum:]._!#$%*\\/&?{}+=\'^~-])+@[[:alnum:]]+[[:alnum:].-]+\\.[[:alpha:]]{2,}){0,1}$/';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID   => "Invalid type given, value should be string, integer or float",
        self::NOT_MATCH => "'%value%' is not an email address (e.g. name@somewhere.com).",
    );

    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected $_pattern = self::EMAIL_REGEX;

    /**
     * Sets validator options
     *
     * @param  string|\Zend_Config $pattern
     * @return void
     */
    public function __construct($pattern = self::EMAIL_REGEX)
    {
        parent::__construct($pattern);
    }
}