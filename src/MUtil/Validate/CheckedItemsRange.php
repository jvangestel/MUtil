<?php
                
/**
 *
 * @package    MUti
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 */

/**
 *
 * @package    MUti
 * @subpackage Validate
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.8
 */
class MUtil_Validate_CheckedItemsRange extends \Zend_Validate_Abstract
{
    const TOO_LESS = 'tooLess';
    const TOO_MUCH = 'tooMuch';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::TOO_LESS => "At least %min% checked value(s) required",
        self::TOO_MUCH => "Not more then %max% checked value(s) allowed",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'min' => '_valMin',
        'max' => '_valMax'
    );

    protected $_ranOnce  = null;
    protected $_valField = null;
    protected $_valMax   = null;
    protected $_valMin   = null;

    /**
     * Constructor for the integer validator
     *
     * @param string $field The field name
     * @param int $min Min value to compare items number with
     * @param int $max Max value to compare items number with
     */
    public function __construct($field, $min, $max)
    {
        $this->_valField = $field;
        $this->_valMin = $min;
        $this->_valMax = $max;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is a valid integer
     *
     * @param  string|integer $value
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        if (isset($this->_ranOnce[$this->_valField]) || ! isset($context[$this->_valField])) {
            return true;
        }

        $this->_ranOnce[$this->_valField] = true;
        if ($this->_valMin > 0 && count($context[$this->_valField]) < $this->_valMin) {
            $this->_error(self::TOO_LESS);
            return false;
        }

        // Value should be less
        if ($this->_valMax > 0 && count($context[$this->_valField]) > $this->_valMax) {
            $this->_error(self::TOO_MUCH);
            return false;
        }
        return true;
    }
}