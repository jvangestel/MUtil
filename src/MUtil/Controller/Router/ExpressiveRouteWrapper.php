<?php

namespace MUtil\Controller\Router;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Helper\UrlHelper;

class ExpressiveRouteWrapper
{

    protected $actionKey = 'action';
    protected $controllerKey = 'controller';
    protected $moduleKey = 'module';

    protected $request;

    protected $urlHelper;

    public function __construct(ServerRequestInterface $request, UrlHelper $urlHelper)
    {
        $this->request = $request;
        $this->urlHelper = $urlHelper;
    }

    public function assemble($data = array(), $reset = false, $encode = false, $partial = false)
    {
        $route = $this->getRoute();
        //$params = $this->request->getAttributes();
        $queryParams = [];
        //$params = $data + $params;
        if ($reset !== true) {
            //$this->request->getQueryParams();
            $requestParams = $this->extractRequestParams($data);
            $queryParams = $requestParams + $queryParams;
        }

        $url = $this->urlHelper->generate($route->getName(), $data, $queryParams);
        return $url;
    }

    public function extractRequestParams($params)
    {
        if (array_key_exists($this->moduleKey, $params)) {
            unset($params[$this->moduleKey]);
        }
        if (array_key_exists($this->controllerKey, $params)) {
            unset($params[$this->controllerKey]);
        }
        if (array_key_exists($this->actionKey, $params)) {
            unset($params[$this->actionKey]);
        }
        return $params;
    }

    public function getRoute()
    {
        $routeResult = $this->request->getAttribute('Zend\Expressive\Router\RouteResult');
        if (is_null($routeResult)) {
            return null; // Probably needs to be some default route
        }
        return $routeResult->getMatchedRoute();
    }
}