<?php

/**
 * PHP 7.3 compatibility class to prevent notices
 *
 *
 * @package    MUtil
 * @subpackage View\Helper
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2019, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 */

/**
 *
 * @package    MUtil
 * @subpackage View\Helper
 * @copyright  Copyright (c) 2019, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.6 24-Sep-2019 12:00:22
 */
class MUtil_View_Helper_HeadLink extends \Zend_View_Helper_HeadLink
{
    /**
     * Create item for stylesheet link item
     *
     * @param  array $args
     * @return stdClass|false Returns fals if stylesheet is a duplicate
     */
    public function createDataStylesheet(array $args)
    {
        $rel                   = 'stylesheet';
        $type                  = 'text/css';
        $media                 = 'screen';
        $conditionalStylesheet = false;
        $href                  = array_shift($args);

        if ($this->_isDuplicateStylesheet($href)) {
            return false;
        }

        if (0 < count($args)) {
            $media = array_shift($args);
            if(is_array($media)) {
                $media = implode(',', $media);
            } else {
                $media = (string) $media;
            }
        }
        if (0 < count($args)) {
            $conditionalStylesheet = array_shift($args);
            if(!empty($conditionalStylesheet) && is_string($conditionalStylesheet)) {
                $conditionalStylesheet = (string) $conditionalStylesheet;
            } else {
                $conditionalStylesheet = null;
            }
        }

        if(0 < count($args) && is_array($args[0])) {
            $extras = array_shift($args);
            $extras = (array) $extras;
        } else {
            $extras = [];
        }

        $attributes = compact('rel', 'type', 'href', 'media', 'conditionalStylesheet', 'extras');
        return $this->createData($this->_applyExtras($attributes));
    }

    /**
     * Create item for alternate link item
     *
     * @param  array $args
     * @return stdClass
     */
    public function createDataAlternate(array $args)
    {
        if (3 > count($args)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception(sprintf('Alternate tags require 3 arguments; %s provided', count($args)));
            $e->setView($this->view);
            throw $e;
        }

        $rel   = 'alternate';
        $href  = array_shift($args);
        $type  = array_shift($args);
        $title = array_shift($args);

        if(0 < count($args) && is_array($args[0])) {
            $extras = array_shift($args);
            $extras = (array) $extras;

            if(isset($extras['media']) && is_array($extras['media'])) {
                $extras['media'] = implode(',', $extras['media']);
            }
        } else {
            $extras = [];
        }

        $href  = (string) $href;
        $type  = (string) $type;
        $title = (string) $title;

        $attributes = compact('rel', 'href', 'type', 'title', 'extras');
        return $this->createData($this->_applyExtras($attributes));
    }
}
