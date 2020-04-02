<?php

/**
 *
 * @package    MUtil
 * @subpackage View\Helper
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

/**
 * Array values switching to hidden now have the correct output
 *
 * @package    MUtil
 * @subpackage View\Helper
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.7.1
 */
class MUtil_View_Helper_FormHidden extends \Zend_View_Helper_FormElement
{
    /**
     * Generates a 'hidden' element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     * @param mixed $value The element value.
     * @param array $attribs Attributes for the element tag.
     * @return string The element XHTML.
     */
    public function formHidden($name, $value = null, array $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable
        if (isset($id)) {
            if (isset($attribs) && is_array($attribs)) {
                $attribs['id'] = $id;
            } else {
                $attribs = array('id' => $id);
            }
        }
        if ($value instanceof \Zend_Date) {
            return $this->_hidden($name, $value->toString('yyyy-MM-dd HH:mm:ss'), $attribs);
        }
        
        // \MUtil_Echo::track($value, $attribs['multiOptions']);
        unset($attribs['multiOptions']);
        if (! is_array($value)) {
            return $this->_hidden($name, $value, $attribs);
        }

        $output = '';
        foreach ($value as $key => $val) {
            $output .= $this->_hidden($name . '[' . $key . ']', $val, $attribs);
        }
        return $output;
    }
}
