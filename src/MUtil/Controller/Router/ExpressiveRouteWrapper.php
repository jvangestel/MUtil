<?php

namespace MUtil\Controller\Router;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Helper\UrlHelper;

class ExpressiveRouteWrapper
{
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
        $params = $this->request->getAttributes();
        $queryParams = $this->request->getQueryParams();
        $params = $data + $params;

        $url = $this->urlHelper->generate($route->getName(), $params, $queryParams);
        return $url;
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