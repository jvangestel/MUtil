<?php

namespace MUtil\Controller;

use MUtil\Controller\Request\ExpressiveRequestWrapper;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;

class ActionAbstract
{
    public $request;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    public function __construct(ExpressiveRequestWrapper $request, UrlHelper $urlHelper)
    {
        $this->request = $request;
        $this->urlHelper = $urlHelper;
        $this->_helper = new \Gems\Legacy\Controller\Action\HelperBroker($this);
    }

    protected function _forward($action, $controller = null, $module = null, array $params = null)
    {
        return $this->forward($action, $controller, $module, $params);
    }

    public function forward($action, $controller = null, $module = null, array $params = null)
    {
        $route = $this->request->getRoute();
        $routeResult = $this->request->getRouteResult();
        $newParams = $routeResult->getMatchedParams();
        $newParams['action'] = $action;
        if (is_array($params)) {
            $newParams += $params;
        }

        $url = $this->urlHelper->generate($route->getName(), $newParams);

        return new RedirectResponse($url);
    }

    protected function _getParam($paramName, $default=null)
    {
        return $this->getParam($paramName, $default);
    }

    public function getParam($paramName, $default=null)
    {
        $value = $this->request->getParam($paramName);
        if ((null === $value || '' === $value) && (null !== $default)) {
            $value = $default;
        }

        return $value;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function dispatch($action)
    {
        // Notify helpers of action preDispatch state
        $this->_helper->notifyPreDispatch();

        $this->preDispatch();
        if ($this->getRequest()->isDispatched()) {
            if (null === $this->_classMethods) {
                $this->_classMethods = get_class_methods($this);
            }

            // If pre-dispatch hooks introduced a redirect then stop dispatch
            // @see ZF-7496
            /** MENNO: Dit werkt nog niet dus even uitgezet
            /*if (!($this->getResponse()->isRedirect())) {
                // preDispatch() didn't change the action, so we can continue
                if ($this->getInvokeArg('useCaseSensitiveActions') || in_array($action, $this->_classMethods)) {
                    if ($this->getInvokeArg('useCaseSensitiveActions')) {
                        trigger_error('Using case sensitive actions without word separators is deprecated; please do not rely on this "feature"');
                    }
                    $this->$action();
                } else {
                    $this->__call($action, array());
                }
            }*/
            $this->postDispatch();
        }

        // whats actually important here is that this action controller is
        // shutting down, regardless of dispatching; notify the helpers of this
        // state
        $this->_helper->notifyPostDispatch();
    }
    
    /**
     * Pre-dispatch routines
     *
     * Called before action method. If using class with
     * {@link Zend_Controller_Front}, it may modify the
     * {@link $_request Request object} and reset its dispatched flag in order
     * to skip processing the current action.
     *
     * @return void
     */
    public function preDispatch()
    {
    }

    /**
     * Post-dispatch routines
     *
     * Called after action method execution. If using class with
     * {@link Zend_Controller_Front}, it may modify the
     * {@link $_request Request object} and reset its dispatched flag in order
     * to process an additional action.
     *
     * Common usages for postDispatch() include rendering content in a sitewide
     * template, link url correction, setting headers, etc.
     *
     * @return void
     */
    public function postDispatch()
    {
    }
    
    /**
     * for compatibility, should probably be removed when possible
     * 
     * @return type
     */
    public function getResponse()
    {
        if (is_null($this->response)) {
            $this->response = new \Zend_Controller_Response_Http();
        }
        
        return $this->response;
    }

}