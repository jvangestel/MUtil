<?php

/**
 * Select decorator to style select elements as Select2 elements.
 * More information and downloads on: https://select2.org/
 * Files needed:
 * select2.min.css
 * select2.min.js
 * select2-bootstrap.min.css <- for bootstrap layouts only. See https://github.com/select2/select2-bootstrap-them
 *
 *
 * The default settings assume that only this decorator is added to the element. This will load all default decorators
 * in the select element
 * This can be disabled in the options
 *
 * Options:
 * cssPath: path where the Select2 css files can be found
 * jsPath: path where the Select2 js files can be found
 * addDefaultDecorators: should the default decorators be loaded to style this element?
 *
 * Class MUtil_Form_Decorator_Select2
 */
class MUtil_Form_Decorator_Select2 extends \Zend_Form_Decorator_Abstract
{
    /**
     * Default basedir for js files. Can be overwritten through the Decorator options.
     * @var string
     */
    protected $_cssPath = 'css';

    /**
     * Default basedir for js files. Can be overwritten through the Decorator options.
     * @var string
     */
    protected $_jsPath = 'js';

    /**
     * @var \Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * @var string filename of select2 js
     */
    protected $_select2JsFilenames = [
        'select2.min.js'
    ];

    /**
     * @var string filename of select2 css
     */
    protected $_select2CssFilenames = [
        'select2.min.css',
    ];

    protected $_select2CssBootstrapFilenames = [
        'select2-bootstrap.min.css'
    ];

    /**
     * @var \Zend_View_Abstract
     */
    protected $_view;

    public function __construct($options = null)
    {
        // If basepath not set, try a default
        if ($options) {
            if (is_array($options)) {
                $this->_options = $options;
            } else {
                $this->_options[] = $options;
            }
            if (isset($this->_options['cssPath'])) {
                $this->_cssPath = $this->_options['cssPath'];
            }
            if (isset($this->_options['jsPath'])) {
                $this->_cssPath = $this->_options['jsPath'];
            }
        }
    }

    /**
     * Render form elements
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $this->_view = $element->getView();
        $this->_request = \MUtil\Controller\Front::getRequest();

        $this->addDefaultFiles();

        $this->addElementSelectEnabler();

        return $content;
    }

    protected function addDefaultFiles()
    {
        static $sayThisOnlyOnce = true;

        if ($sayThisOnlyOnce) {
            if ($fileLocations = $this->getJsFiles()) {
                foreach ($fileLocations as $fileLocation) {
                    $this->_view->headScript()->appendFile($fileLocation);
                }
            }

            if ($fileLocations = $this->getCssFiles()) {
                foreach ($fileLocations as $fileLocation) {
                    $this->_view->headLink()->appendStylesheet($fileLocation);
                }
            }
            $sayThisOnlyOnce = false;
        }
    }

    protected function addElementSelectEnabler()
    {
        $id = $this->getElementId();

        $select2 = 'select2';
        $js = sprintf('%s(document).ready(function() { %s("#%s").%s(); });',
            \ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
            \ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
            $id,
            $select2
        );

        $this->_view->headScript()->appendScript($js);
    }

    protected function getCssFiles()
    {
        if ($this->_cssPath) {

            $cssUrls = [];

            foreach($this->_select2CssFilenames as $filename) {
                $cssUrls[] = $this->_request->getBasePath() . '/' . $this->_cssPath . '/' . $filename;
            }
            if (\MUtil_Bootstrap::enabled()) {
                foreach($this->_select2CssBootstrapFilenames as $filename) {
                    $cssUrls[] = $this->_request->getBasePath() . '/' . $this->_cssPath . '/' . $filename;
                }
            }

            return $cssUrls;
        }
        return null;
    }

    protected function getElementId()
    {
        $element = $this->getElement();
        $name = $element->getName();
        $attribs = $element->getAttribs();
        if (isset($attribs['id'])) {
            return $attribs['id'];
        }
        return str_replace('[]', '', $name);
    }

    protected function getJsFiles()
    {
        if ($this->_jsPath) {
            foreach($this->_select2JsFilenames as $filename) {
                $jsUrls[] = $this->_request->getBasePath() . '/' . $this->_jsPath . '/' . $filename;
            }
            return $jsUrls;
        }
        return null;
    }
}
