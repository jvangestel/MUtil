<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Mail
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

/**
 * Extends standard \Zend_Mail with functions for using HTML templates for all mails
 * and adding content using BB Code text.
 *
 * @package    MUtil
 * @subpackage Mail
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
class MUtil_Mail extends \Zend_Mail implements \MUtil_Registry_TargetInterface
{
    /**
     * HTML Template for html part of the message
     *
     * @var string
     */
    protected $_htmlTemplate;

    /**
     * Called after the check that all required registry values
     * have been set correctly has run.
     *
     * @return void
     */
    public function afterRegistry()
    { }

    /**
     * Allows the loader to set resources.
     *
     * @param string $name Name of resource to set
     * @param mixed $resource The resource.
     * @return boolean True if $resource was OK
     */
    public function answerRegistryRequest($name, $resource)
    {
        if (\MUtil_Registry_Source::$verbose) {
            \MUtil_Echo::r('Resource set: ' . get_class($this) . '->' . __FUNCTION__ .
            '("' . $name . '", ' .
            (is_object($resource) ? get_class($resource) : gettype($resource)) . ')');
        }
        $this->$name = $resource;

        return true;
    }

    /**
     * Should be called after answering the request to allow the Target
     * to check if all required registry values have been set correctly.
     *
     * @return boolean False if required values are missing.
     */
    public function checkRegistryRequestsAnswers()
    {
        return true;
    }

    /**
     * Filters the names that should not be requested.
     *
     * Can be overriden.
     *
     * @param string $name
     * @return boolean
     */
    protected function filterRequestNames($name)
    {
        return '_' !== $name[0];
    }

    /**
     * Allows the loader to know the resources to set.
     *
     * Returns those object variables defined by the subclass but not at the level of this definition.
     *
     * Can be overruled.
     *
     * @return array of string names
     */
    public function getRegistryRequests()
    {
        return array_filter(array_keys(get_object_vars($this)), array($this, 'filterRequestNames'));
    }

    /**
     * Returns the the current template
     *
     * @return string
     */
    public function getHtmlTemplate()
    {
        if (! $this->_htmlTemplate) {
            $this->_htmlTemplate = "<html><body>\n{content}\n</body></html>";
        }

        return $this->_htmlTemplate;
    }

    /**
     * Set both the Html and Text versions of a message
     *
     * @param string $content
     * @return \MUtil_Mail (continuation pattern)
     */
    public function setBodyBBCode($content)
    {
        $this->setBodyHtml(\MUtil_Markup::render($content, 'Bbcode', 'Html'));
        $this->setBodyText(\MUtil_Markup::render($content, 'Bbcode', 'Text'));

        return $this;
    }

    /**
     * Sets the HTML body for the message, using a template for html if it exists/
     *
     * @param  string    $html
     * @param  string    $charset
     * @param  string    $encoding
     * @return \MUtil_Mail (continuation pattern)
     */
    public function setBodyHtml($html, $charset = null, $encoding = \Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        if ($template = $this->getHtmlTemplate()) {
            $html = str_replace('{content}', $html, $template);
        }

        return parent::setBodyHtml($html, $charset, $encoding);
    }

    /**
     * Set's a html template in which the message content is placed.
     *
     * @param string $template
     * @return \MUtil_Mail \MUtil_Mail (continuation pattern)
     */
    public function setHtmlTemplate($template)
    {
        $this->_htmlTemplate = $template;
        return $this;
    }

    /**
     * Set the basic html template with the content of a filename
     *
     * @param string $filename
     * @return \MUtil_Mail (continuation pattern)
     */
    public function setHtmlTemplateFile($filename)
    {
        if (file_exists($filename)) {
            $this->setHtmlTemplate(file_get_contents($filename));
        }
        return $this;
    }
}

