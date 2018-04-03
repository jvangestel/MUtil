<?php

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2017, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2017, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 * @since      Class available since version 1.8.4 03-Apr-2018 15:51:24
 */
class MUtil_Validate_NoCsvInjectionChars extends \MUtil_Validate_Regexclude
{
    const SCRIPT_REGEX = '/[=+|]/';

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

        $this->_messageTemplates[parent::MATCH] = "The characters =, + and | are not allowed here.";
    }
}
