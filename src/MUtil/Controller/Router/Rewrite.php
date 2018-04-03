<?php

/**
 *
 * @package    MUtil
 * @subpackage Controller\Router
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2017, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace MUtil\Controller\Router;

/**
 *
 * @package    MUtil
 * @subpackage Controller\Router
 * @copyright  Copyright (c) 2017, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 * @since      Class available since version 1.8.4 03-Apr-2018 15:07:10
 */
class Rewrite extends \Zend_Controller_Router_Rewrite
{
    /**
     *
     * @var array containing ALL parameters, including without value
     */
    protected $_allParams = [];

    /**
     * Sets parameters for request object
     *
     * Module name, controller name and action name
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param array                            $params
     */
    protected function _setRequestParams($request, $params)
    {
        $this->_allParams = $this->_allParams + $params;

        parent::_setRequestParams($request, $params);
    }

    /**
     *
     * @return array ALL set parameters, including those without value
     */
    public function getAllParams()
    {
        return $this->_allParams;
    }
}
