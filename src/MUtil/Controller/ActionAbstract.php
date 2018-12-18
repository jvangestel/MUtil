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
}