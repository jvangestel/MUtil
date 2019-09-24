<?php

/**
 *
 * @package    MUtil
 * @subpackage Less
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

/**
 * Make sure each .less css script is compiled to .css
 *
 * @package    MUtil
 * @subpackage Less
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class MUtil_Less_View_Helper_HeadLink extends \MUtil_View_Helper_HeadLink
{
    /**
     * Absolute file path to the webroot
     *
     * @var string
     */
    protected $_webroot;

    public function __construct()
    {
        parent::__construct();
        $this->_webroot = dirname($_SERVER["SCRIPT_FILENAME"]);
    }
    /**
     * Utility function to base64 encode gradients for use in IE
     *
     * @param type $args
     * @return type
     */
    public function base64encode($args)
    {
        list($type, $value, $unit) = $args;

        $unit = array(base64_encode( $this->compileValue($args)));

        return array('string', '"', $unit);
    }

    /**
     * Copied mostly from less.inc.php
     *
     * Needed since it is protected
     *
     * @param type $value
     * @return type
     */
    protected function compileValue($value)
    {
		switch ($value[0]) {
		case 'list':
			// [1] - delimiter
			// [2] - array of values
			return implode($value[1], array_map(array($this, 'compileValue'), $value[2]));
		case 'keyword':
			// [1] - the keyword
			return $value[1];
		case 'number':
			list(, $num, $unit) = $value;
			// [1] - the number
			// [2] - the unit
			if ($this->numberPrecision !== null) {
				$num = round($num, $this->numberPrecision);
			}
			return $num . $unit;
		case 'string':
			// [1] - contents of string (includes quotes)
			list(, $delim, $content) = $value;
			foreach ($content as &$part) {
				if (is_array($part)) {
					$part = $this->compileValue($part);
				}
			}
			return $delim . implode($content) . $delim;
		default: // assumed to be unit
			$this->throwError("unknown value type: $value[0]");
		}
    }

    /**
     * Compile a less file
     *
     * @param \Zend_View $view
     * @param string $href The less file
     * @param boolean $always Always compile
     * @return boolean True when changed
     */
    public function compile(\Zend_View $view, $href, $always = false)
    {
        if (\MUtil_String::startsWith($href, 'http', true)) {
            // When a local url, strip the serverUrl and basepath
            $base = $view->serverUrl() . $view->baseUrl();
            if (\MUtil_String::startsWith($href, $base, true)) {
                // Only strip when urls match
                $href = substr($href, strlen($base));
            }
        }

        // Add full path to the webdir
        $inFile  = $this->_webroot . '/' .  $href;
        $outFile = substr($inFile, 0, -strlen(pathinfo($inFile, PATHINFO_EXTENSION))) . 'css';

        // Try compiling
        try {
            // \MUtil_Echo::track($inFile, $outFile);

            $lessc = new lessc();
            $lessc->setOption('relativeUrls', true);
            $lessc->registerFunction('base64encode', array($this, 'base64encode'));
            if ($always || array_key_exists('compilecss', \Zend_Controller_Front::getInstance()->getRequest()->getParams())) {
                $result = (boolean) $lessc->compileFile($inFile, $outFile);
            } else {
                $result = $lessc->checkedCompile($inFile, $outFile);
            }
        } catch (\Exception $exc) {
            // If we have an error, present it if not in production
            if ((APPLICATION_ENV !== 'production') || (APPLICATION_ENV !== 'acceptance')) {
                \MUtil_Echo::pre($exc->getMessage());
            }
            $result = null;
        }

        return $result;
    }

    /**
     * Create HTML link element from data item
     *
     * @param  \stdClass $item
     * @return string
     */
    public function itemToString(\stdClass $item)
    {
        $attributes = (array) $item;

        if (isset($attributes['type']) &&
                (($attributes['type'] == 'text/css') || ($attributes['type'] == 'text/less'))) {

            // This is a stylesheet, consider extension and compile .less to .css
            if (($attributes['type'] == 'text/less') || \MUtil_String::endsWith($attributes['href'], '.less', true)) {
                $this->compile($this->view, $attributes['href'], false);

                // Modify object, not the derived array
                $item->type = 'text/css';
                $item->href = substr($attributes['href'], 0, -4) . 'css';
            }
        }

        return parent::itemToString($item);
    }
}
