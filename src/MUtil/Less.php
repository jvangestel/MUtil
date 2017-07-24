<?php

/**
 *
 * @package    MUtil
 * @subpackage Less
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

/**
 * By enabling Less for a view each .less css file is compiled to .css and output as such
 *
 * @package    MUtil
 * @subpackage Less
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.6.5
 */
class MUtil_Less
{
    /**
     * Less-enable a view instance
     *
     * @param  \Zend_View_Interface $view
     * @return void
     */
    public static function enableView(\Zend_View_Interface $view)
    {
        if (false === $view->getPluginLoader('helper')->getPaths('MUtil_Less_View_Helper')) {
            $view->addHelperPath('MUtil/Less/View/Helper', 'MUtil_Less_View_Helper');
        }
    }
}
