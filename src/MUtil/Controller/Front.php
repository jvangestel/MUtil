<?php

namespace MUtil\Controller;

use MUtil\Controller\Response\ExpressiveResponseWrapper;
use MUtil\Controller\Router\ExpressiveRouteWrapper;
use MUtil\Controller\Request\ExpressiveRequestWrapper;

class Front
{
    /**
     * @var
     */
    protected static $request;

    /**
     * @var ExpressiveResponseWrapper
     */
    protected static $response;

    protected static $router;

    public static function setRequest(ExpressiveRequestWrapper $request)
    {
        self::$request = $request;
    }
    
    public static function setLegacyRequest(\Zend_Controller_Request_Http $request)
    {
        self::$request = $request;
    }

    public static function getRequest()
    {
        if (is_null(self::$request)) {
            self::$request = \Zend_Controller_Front::getInstance()->getRequest();
        }
        return self::$request;
    }

    public static function getResponse()
    {
        if (is_null(self::$response)) {
            self::$response = \Zend_Controller_Front::getInstance()->getResponse();
        }
        return self::$response;
    }

    public static function setResponse(ExpressiveResponseWrapper $response)
    {
        self::$response = $response;
    }

    public static function setRouter(ExpressiveRouteWrapper $router)
    {
        self::$router = $router;
    }

    public static function getRouter()
    {
        if (is_null(self::$router)) {
            self::$router = \Zend_Controller_Front::getInstance()->getRouter();
        }
        return self::$router;
    }
}