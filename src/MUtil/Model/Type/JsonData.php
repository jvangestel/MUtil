<?php

/**
 *
 * @package    MUtil
 * @subpackage Model_Type
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Type;

/**
 *
 * @package    MUtil
 * @subpackage Model_Type
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.7.1 16-apr-2015 15:30:45
 */
class JsonData
{
    /**
     * Maximum number of items in table display
     * @var int
     */
    private $_maxTable = 3;

    /**
     * Show there are more items
     *
     * @var string
     */
    private $_more = '...';

    /**
     * The separator for the table items
     *
     * @var string
     */
    private $_separator;

    /**
     *
     * @param int $maxTable Max number of rows to display in table display
     * @param string $separator Separator in table display
     * @param string $more There is more in table display
     */
    public function __construct($maxTable = 3, $separator = '<br />', $more = '...')
    {
        $this->_maxTable  = $maxTable;
        $this->_more      = $more;
        $this->_separator = $separator;
    }

    /**
     * Use this function for a default application of this type to the model
     *
     * @param \MUtil_Model_ModelAbstract $model
     * @param string $name The field to set the seperator character
     * @param boolean $detailed When true show detailed information
     * @return \MUtil\Model\Type\JsonData (continuation pattern)
     */
    public function apply(\MUtil_Model_ModelAbstract $model, $name, $detailed)
    {
        if ($detailed) {
            $formatFunction = 'formatDetailed';
        } else {
            $formatFunction = 'formatTable';
        }
        $model->set($name, 'formatFunction', array($this, $formatFunction));
        $model->setOnLoad($name, array($this, 'loadValue'));
        $model->setOnSave($name, array($this, 'saveValue'));
    }

    /**
     * Displays the content
     *
     * @param string $value
     * @return string
     */
    public function formatDetailed($value)
    {
        if ((null === $value) || is_scalar($value)) {
            return $value;
        }
        if (! is_array($value)) {
                return \MUtil_Html_TableElement::createArray($value)
                        ->appendAttrib('class', 'jsonNestedObject');
        }
        foreach ($value as $key => $val) {
            if (! (is_int($key) && (is_scalar($val) || ($val instanceof \MUtil_Html_HtmlInterface)))) {
                return \MUtil_Html_TableElement::createArray($value)
                        ->appendAttrib('class', 'jsonNestedArray');
            }
        }
        return \MUtil_Html::create('ul', $value, array('class' => 'jsonArrayList'));
    }

    /**
     * Displays the content
     *
     * @param string $value
     * @return string
     */
    public function formatTable($value)
    {
        if ((null === $value) || is_scalar($value)) {
            return $value;
        }
        if (is_array($value)) {
            $i = 0;
            $output = new \MUtil_Html_Sequence();
            $output->setGlue($this->_separator);
            foreach ($value as $val) {
                if ($i++ > $this->_maxTable) {
                    $output->append($this->_more);
                    break;
                }
                $output->append($val);
            }
            return $output;
        }
        return \MUtil_Html_TableElement::createArray($value);
    }

    /**
     * A ModelAbstract->setOnLoad() function that concatenates the
     * value if it is an array.
     *
     * @see \MUtil_Model_ModelAbstract
     *
     * @param mixed $value The value being saved
     * @param boolean $isNew True when a new item is being saved
     * @param string $name The name of the current field
     * @param array $context Optional, the other values being saved
     * @param boolean $isPost True when passing on post data
     * @return array Of the values
     */
    public function loadValue($value, $isNew = false, $name = null, array $context = array(), $isPost = false)
    {
        return json_decode($value, true);
    }

    /**
     * A ModelAbstract->setOnSave() function that concatenates the
     * value if it is an array.
     *
     * @see \MUtil_Model_ModelAbstract
     *
     * @param mixed $value The value being saved
     * @param boolean $isNew True when a new item is being saved
     * @param string $name The name of the current field
     * @param array $context Optional, the other values being saved
     * @return string Of the values concatenated
     */
    public function saveValue($value, $isNew = false, $name = null, array $context = array())
    {
        if ($value === null) {
            return null;
        }
        return json_encode($value);
    }
}
