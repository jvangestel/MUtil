<?php

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2016, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2016, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 * @since      Class available since version 1.8.2 Feb 7, 2017 5:03:48 PM
 */
class MUtil_Validate_NoTags extends \MUtil_Validate_Regexclude
{
    const NOTAGS_REGEX = '/[<&][a-zA-Z\\:]/';

    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected $_pattern = self::NOTAGS_REGEX;

    /**
     * Sets validator options
     *
     * @param  string|\Zend_Config $pattern
     * @return void
     */
    public function __construct($pattern = self::NOTAGS_REGEX)
    {
        parent::__construct($pattern);

        $this->_messageTemplates[parent::MATCH] = "No letters, ':' or '\\' are allowed directly after a '<' or '&' character.";
    }

    /**
     * Defined by \Zend_Validate_Interface
     *
     * Returns true if and only if $value matches against the pattern option
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if ((null === $value) || ('' == $value) || (is_array($value) && empty($value)) || is_object($value)) {
            return true;
        }

        return parent::isValid($value);
    }
}
