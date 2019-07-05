<?php

namespace MUtil\Controller\Response;

use Zend_Controller_Response_Exception;

class ExpressiveResponseWrapper
{
    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    public function __construct(\Psr\Http\Message\ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Retrieve HTTP response code
     *
     * @return int
     */
    public function getHttpResponseCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set redirect URL
     *
     * Sets Location header and response code. Forces replacement of any prior
     * redirects.
     *
     * @param string $url
     * @param int $code
     * @return ExpressiveResponseWrapper
     */
    public function setRedirect($url, $code = 302)
    {
        $this->canSendHeaders(true);
        $this->response = $this->response->withHeader('Location', $url, true)
            ->withStatus($code);

        return $this;
    }

    /**
     * Can we send headers?
     *
     * @param boolean $throw Whether or not to throw an exception if headers have been sent; defaults to false
     * @return boolean
     * @throws Zend_Controller_Response_Exception
     */
    public function canSendHeaders($throw = false)
    {
        $ok = headers_sent($file, $line);
        if ($ok && $throw && $this->headersSentThrowsException) {
            throw new \Zend_Controller_Response_Exception('Cannot send headers; headers already sent in ' . $file . ', line ' . $line);
        }

        return !$ok;
    }


}