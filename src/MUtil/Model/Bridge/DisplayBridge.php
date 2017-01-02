<?php

/**
 *
 * @package    MUtil
 * @subpackage Model\Bridge
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

/**
 *
 * @package    MUtil
 * @subpackage Model\Bridge
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since 2014
 */
class MUtil_Model_Bridge_DisplayBridge extends \MUtil_Model_Bridge_BridgeAbstract
{
    /**
     * Return an array of functions used to process the value
     *
     * @param string $name The real name and not e.g. the key id
     * @return array
     */
    protected function _compile($name)
    {
        $output = array();

        if ($this->model->has($name, 'multiOptions')) {
            $options = $this->model->get($name, 'multiOptions');

            $output['multiOptions'] = function ($value) use ($options) {
                if (null === $value) {
                    return isset($options['']) ? $options[''] : null;
                }
                return is_scalar($value) && array_key_exists($value, $options) ? $options[$value] : $value;
            };
        }

        if ($this->model->has($name, 'formatFunction')) {
            $output['formatFunction'] = $this->model->get($name, 'formatFunction');

        } elseif ($this->model->has($name, 'dateFormat')) {
            $format = $this->model->get($name, 'dateFormat');
            if (is_callable($format)) {
                $output['dateFormat'] = $format;
            } else {
                $storageFormat = $this->model->get($name, 'storageFormat');
                $output['dateFormat'] = function ($value) use ($format, $storageFormat) {
                    return \MUtil_Date::format($value, $format, $storageFormat);
                };
            }
        } elseif ($this->model->has($name, 'numberFormat')) {
            $format = $this->model->get($name, 'numberFormat');
            if (is_callable($format)) {
                $output['numberFormat'] = $format;
            } else {
                $output['numberFormat'] = function ($value) use ($format) {
                    return \Zend_Locale_Format::toNumber($value, array('number_format' => $format));
                };
            }
        }

        if ($this->model->has($name, 'markCallback')) {
            $output['markCallback'] = $this->model->get($name, 'markCallback');
        }

        return $output;
    }
}
