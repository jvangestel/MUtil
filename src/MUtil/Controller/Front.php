<?php

namespace MUtil\Controller;

use MUtil\Controller\Router\ExpressiveRouteWrapper;
use MUtil\Controller\Request\ExpressiveRequestWrapper;

class Front
{
    /**
     * @var
     */
    protected static $request;

    protected static $router;

    public static function setRequest(ExpressiveRequestWrapper $request)
    {
        self::$request = $request;
    }

    public static function getRequest()
    {
        return self::$request;
    }

    public static function setRouter(ExpressiveRouteWrapper $router)
    {
        self::$router = $router;
    }

    public static function getRouter()
    {
        return self::$router;
    }
}