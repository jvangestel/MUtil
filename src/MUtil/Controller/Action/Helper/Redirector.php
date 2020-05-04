<?php


class MUtil_Controller_Action_Helper_Redirector extends \Zend_Controller_Action_Helper_Redirector
{
    /**
     * getRequest() -
     *
     * @return Zend_Controller_Request_Abstract $request
     */
    public function getRequest()
    {
        return \MUtil\Controller\Front::getRequest();
    }

    /**
     * getResponse() -
     *
     * @return Zend_Controller_Response_Abstract $response
     */
    public function getResponse()
    {
        return \MUtil\Controller\Front::getResponse();
    }

    /**
     * exit(): Perform exit for redirector
     *
     * @return void
     */
    public function redirectAndExit()
    {
        if ($this->getCloseSessionOnExit()) {
            // Close session, if started
            if (class_exists('Zend_Session', false) && Zend_Session::isStarted()) {
                Zend_Session::writeClose();
            } elseif (isset($_SESSION)) {
                session_write_close();
            }
        }

        $response = $this->getResponse();
        if (!$response instanceof \MUtil\Controller\Response\ExpressiveResponseWrapper) {
            $this->getResponse()->sendHeaders();
            exit();
        }
    }

    /**
     * Build a URL based on a route
     *
     * @param  array   $urlOptions
     * @param  string  $name Route name
     * @param  boolean $reset
     * @param  boolean $encode
     * @return void
     */
    public function setGotoRoute(array $urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
        $router = \MUtil\Controller\Front::getRouter();
        $url    = $router->assemble($urlOptions, $name, $reset, $encode);

        $this->_redirect($url);
    }
}