<?php

/**
 *
 * @package    MUtil
 * @subpackage Form
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 */

/**
 * Extends a \Zend_Form with automatic JQuery activation,
 * \MUtil_Html rendering integration and non-css stylesheet per
 * form (possibly automatically calculated) fixed label widths.
 *
 * @see \MUtil_Html
 *
 * @package    MUtil
 * @subpackage Form
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class MUtil_Form extends \Zend_Form implements \MUtil_Registry_TargetInterface
{
    /**
     * This variable holds all the stylesheets attached to this form
     *
     * @var array
     */
    protected $_css = array();

    /**
     * This variable holds all the scripts attached to this form
     *
     * @var array
     */
	protected $_scripts = null;

    /**
     * The order in which the element parts should be displayed
     * when using a fixed or dynamic label width.
     *
     * @var array
     */
    protected $_displayOrder = array('element', 'errors', 'description');

    /**
     * $var \MUtil_HtmlElement
     */
    protected $_html_element;

    /**
     * Option value for fixed label width for label elements redered with \MUtil_Html_LabelElement
     *
     * @see \MUtil_Html_LabelElement
     *
     * @var int
     */
    protected $_labelWidth;

    /**
     * Option value to set the fixed label with for label elements redered with
     * \MUtil_Html_LabelElement by takeing the strlen of the longest label times
     * this factor
     *
     * @see \MUtil_Html_LabelElement
     *
     * @var float
     */
    protected $_labelWidthFactor;

    /**
     * Is Bootstrap activated for this form?
     *
     * @var boolean
     */
    protected $_no_bootstrap = true;

    /**
     * Is JQuery activated for this form?
     *
     * @var boolean
     */
    protected $_no_jquery = true;

    /**
     * False or a lazy instance of this form
     *
     * @var mixed
     */
    protected $_Lazy = false;

    /**
     * The id of the element that keeps track of the focus
     *
     * Set to false to disable
     *
     * @var string
     */
    public $focusTrackerElementId = 'auto_form_focus_tracker';

    /**
     * Constructor
     *
     * Registers form view helper as decorator
     *
     * @param string $name
     * @param mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->addElementPrefixPath('MUtil_Form_Decorator', 'MUtil/Form/Decorator',  \Zend_Form_Element::DECORATOR);
        $this->addElementPrefixPath('MUtil_Validate',       'MUtil/Validate/',       \Zend_Form_Element::VALIDATE);

        parent::__construct($options);

        if ($this->focusTrackerElementId) {
            $this->activateJQuery();

            $elementId = $this->focusTrackerElementId;

            if (\MUtil_Bootstrap::enabled()) {
                $element = new \MUtil\Bootstrap\Form\Element\Hidden($elementId);
            } else {
                $element = new \MUtil\Form\Element\Hidden($elementId);
            }

            $this->addElement($element);

            $script    = sprintf("
                jQuery('form input, form select, form textarea').focus(
                function () {
                    var input = jQuery(this);
                    var tracker = input.closest('form').find('input[name=%s]');
                    tracker.val(input.attr('id'));
                }
                );
                ", $elementId);

            $jquery = $this->getView()->jQuery();
            $jquery->addOnLoad($script);
        }
    }

    /**
     * Activate JQuery for the view
     *
     * @param \Zend_View_Interface $view
     * @return void
     */
    protected function _activateJQueryView(\Zend_View_Interface $view = null)
    {
        if ($this->_no_jquery) {
            return;
        }

        if (null === $view) {
            $view = $this->getView();
            if (null === $view) {
                return;
            }
        }

        \MUtil_JQuery::enableView($view);

        if (false === $view->getPluginLoader('helper')->getPaths('MUtil_JQuery_View_Helper')) {
            $view->addHelperPath('MUtil/JQuery/View/Helper', 'MUtil_JQuery_View_Helper');
        }
    }

    /**
     * Activate Bootstrap for this form
     *
     * @return \MUtil_Form (continuation pattern)
     */
    public function activateBootstrap()
    {
        if ($this->_no_bootstrap) {

            $this->addPrefixPath('MUtil_Bootstrap_Form_Element', 'MUtil/Bootstrap/Form/Element/', \Zend_Form::ELEMENT);
            $this->_no_bootstrap = false;
            $this->_defaultDisplayGroupClass = 'MUtil_Bootstrap_Form_DisplayGroup';
        }

        return $this;
    }

    /**
     * Activate JQuery for this form
     *
     * @return \MUtil_Form (continuation pattern)
     */
    public function activateJQuery()
    {
        if ($this->_no_jquery) {
            \MUtil_JQuery::enableForm($this);

            //$this->addPrefixPath('MUtil_JQuery_Form_Decorator', 'MUtil/JQuery/Form/Decorator/', \Zend_Form::DECORATOR);
            $this->addPrefixPath('MUtil_JQuery_Form_Element', 'MUtil/JQuery/Form/Element/', \Zend_Form::ELEMENT);

            $this->_activateJQueryView();

            $this->_no_jquery = false;
        }

        return $this;
    }

    /**
     * Attach a css file to the form with form-specific css
     *
     * Optional media parameter can be used to determine media-type (print, screen etc)
     *
     * @param string $file
     * @param string $media
     */
    public function addCss($file, $media = '')
    {
    	$this->_css[$file] = $media;
    }

    /**
     * Add a new element
     *
     * $element may be either a string element type, or an object of type
     * \Zend_Form_Element. If a string element type is provided, $name must be
     * provided, and $options may be optionally provided for configuring the
     * element.
     *
     * If a \Zend_Form_Element is provided, $name may be optionally provided,
     * and any provided $options will be ignored.
     *
     * @param  string|\Zend_Form_Element $element
     * @param  string $name
     * @param  array|\Zend_Config $options
     * @throws \Zend_Form_Exception on invalid element
     * @return \Zend_Form (continuation pattern)
     */
    public function addElement($element, $name = null, $options = null)
    {
        parent::addElement($element, $name, $options);

        if (null === $name) {
            $name = $element->getName();
        } else {
            $element = $this->getElement($name);
        }
        if ($this->_no_jquery && ($element instanceof \ZendX_JQuery_Form_Element_UiWidget)) {
            $this->activateJQuery();
        }
        if ($element instanceof \Zend_Form_Element_File) {
            $this->setAttrib('enctype', 'multipart/form-data');
        }
        $element->setDisableTranslator($this->translatorIsDisabled());

        if (isset($options['addDecorators'])) {
            $element->addDecorators($options['addDecorators']);
        }

        return $this;
    }

    /**
     * Add a script to the head
     *
     * @param sring $script name of script, located in baseurl/js/
     * @return \Gems_Form (continuation pattern)
     */
    public function addScript($script)
    {
    	if (is_array($this->_scripts) && in_array($script, $this->_scripts)) {
            return $this;
        }
    	$this->_scripts[] = $script;

        return $this;
    }

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
     * When an element is created this way, the element will have translation which can not
     * be undone in the addElement method. To fix this we add the current translation status
     * of the form to the options
     *
     * @param type $type
     * @param type $name
     * @param type $options
     */
    public function createElement($type, $name, $options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        $options = (array) $options + [
            'disableTranslator' => $this->translatorIsDisabled()
            ];
        return parent::createElement($type, $name, $options);
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
     * Return form specific css
     *
     * @return array
     */
    public function getCss()
    {
    	return $this->_css;
    }

    /**
     * The order in which the element parts should be displayed
     * when using a fixed or dynamic label width.
     *
     * @see setLabelWidth
     *
     * @return array Array containing element parts like 'element', 'errors' and 'description'
     */
    public function getDisplayOrder()
    {
        return $this->_displayOrder;
    }

    /**
     * Returns an Html element that is used to render the form contents.
     *
     * @return \MUtil_Html_HtmlElement Or an equivalent class
     */
    public function getHtml()
    {
        if (! $this->_html_element) {
            foreach ($this->_decorators as $decorator) {
                if ($decorator instanceof \MUtil_Html_ElementDecorator) {
                    break;
                }
            }
            if ($decorator instanceof \MUtil_Html_ElementDecorator) {
                $this->_html_element = $decorator->getHtmlElement();
            } else {
                $this->setHtml();
            }
        }

        return $this->_html_element;
    }


    /**
     * Value to set the fixed label with for label elements redered with
     * \MUtil_Html_LabelElement by takeing the strlen of the longest label times
     * this factor
     *
     * @see \MUtil_Html_LabelElement
     *
     * @var float
     */
    public function getLabelWidth()
    {
        return $this->_labelWidth;
    }

    /**
     * Value to set the fixed label with for label elements redered with
     * \MUtil_Html_LabelElement by takeing the strlen of the longest label times
     * this factor
     *
     * @see \MUtil_Html_LabelElement
     *
     * @var float
     */
    public function getLabelWidthFactor()
    {
        return $this->_labelWidthFactor;
    }

    /**
     * Retrieve plugin loader for given type
     *
     * $type may be one of:
     * - decorator
     * - element
     *
     * If a plugin loader does not exist for the given type, defaults are
     * created.
     *
     * @param  string $type
     * @return \Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader($type = null)
    {
        $type = strtoupper($type);
        if (!isset($this->_loaders[$type])) {
            switch ($type) {
                case self::DECORATOR:
                    $prefixSegment = 'Form_Decorator';
                    $pathSegment   = 'Form/Decorator';
                    break;
                case self::ELEMENT:
                    $prefixSegment = 'Form_Element';
                    $pathSegment   = 'Form/Element';
                    break;
                default:
                    require_once 'Zend/Form/Exception.php';
                    throw new \Zend_Form_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
            }

            $this->_loaders[$type] = new \MUtil_Loader_PluginLoader(array(
                'Zend_'  . $prefixSegment . '_' => 'Zend/'  . $pathSegment . '/',
                'MUtil_' . $prefixSegment . '_' => 'MUtil/' . $pathSegment . '/',
                ));
        }

        return $this->_loaders[$type];
    }

    /**
     * Return form specific javascript
     *
     * @return array
     */
	public function getScripts() {
    	return $this->_scripts;
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
        // \MUtil_Echo::track(array_filter(array_keys(get_object_vars($this)), array($this, 'filterRequestNames')));
        return array_filter(array_keys(get_object_vars($this)), array($this, 'filterRequestNames'));
    }

    /**
     * Return true when the form is lazy
     *
     * @return boolean
     */
    public function isLazy()
    {
        return $this->_Lazy;
    }

    /**
     * Validate the form
     *
     * As it is better for translation utilities to set the labels etc. translated,
     * the MUtil default is to disable translation.
     *
     * However, this also disables the translation of validation messages, which we
     * cannot set translated. The MUtil form is extended so it can make this switch.
     *
     * @param  array   $data
     * @param  boolean $disableTranslateValidators Extra switch
     * @return boolean
     */
    public function isValid($data, $disableTranslateValidators = null)
    {
        if (null !== $disableTranslateValidators) {
            if ($disableTranslateValidators !== $this->translatorIsDisabled()) {
                $oldTranslations = $this->translatorIsDisabled();
                $this->setDisableTranslator($disableTranslateValidators);
            }
        }

        $valid = parent::isValid($data);

        if (isset($oldTranslations)) {
            $this->setDisableTranslator($oldTranslations);
        }

        return $valid;
    }

    /**
     * Load the default decorators
     *
     * @return void
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('AutoFocus', ['form' => $this])
                 ->addDecorator('FormElements');
            if (!\MUtil_Bootstrap::enabled()) {
                $this->addDecorator('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form'));
            }
            $this->addDecorator('Form');
        }
    }

    /**
     * Filter a name to only allow valid variable characters
     *
     * @param  string $value
     * @param  bool $allowBrackets
     * @return string
     */
    public static function normalizeName($value, $allowBrackets = false)
    {
        $charset = '^a-zA-Z0-9_\x7f-\xff';
        if ($allowBrackets) {
            $charset .= '\[\]';
        }
        return preg_replace('/[' . $charset . ']/', '', (string) $value);
    }

    /**
     * Indicate whether or not translation should be disabled
     *
     * Added cascading to elements
     *
     * @param  bool $flag
     * @return \MUtil_Form
     */
    public function setDisableTranslator($flag)
    {
        $flag = (bool) $flag;
        if ($flag !== $this->translatorIsDisabled()) {
            parent::setDisableTranslator($flag);

            // Propagate to elements
            foreach ($this as $element) {
                $element->setDisableTranslator($flag);
            }

            // And propagate to displaygroup elements
            foreach($this->getDisplayGroups() as $displayGroup)
            {
                foreach($displayGroup->getElements() as $element) {
                    $element->setDisableTranslator($flag);
                }
            }
        }

        return $this;
    }

    /**
     * The order in which the element parts should be displayed
     * when using a fixed or dynamic label width.
     *
     * @see setLabelWidth
     *
     * @param array $order Array containing element parts like 'element', 'errors' and 'description'
     * @return \MUtil_Form (continuation pattern)
     */
    public function setDisplayOrder(array $order)
    {
        $this->_displayOrder = $order;

        return $this;
    }

    /**
     * Sets the layout to the use of html elements
     *
     * @see \MUtil_Html
     *
     * @param string $html HtmlTag for element or empty sequence when empty
     * @param string $args \MUtil_Ra::args additional arguments for element
     * @return \MUtil_Form (continuation pattern)
     */
    public function setHtml($html = null, $args = null)
    {
        $options = \MUtil_Ra::args(func_get_args(), 1);

        if ($html instanceof \MUtil_Html_ElementInterface) {
            if ($options) {
                foreach ($options as $name => $option) {
                    if (is_int($name)) {
                        $html[] = $option;
                    } else {
                        $html->$name = $option;
                    }
                }
            }
        } elseif (null == $html) {
            $html = new \MUtil_Html_Sequence($options);
        } else {
            $html = \MUtil_Html::createArray($html, $options);
        }

        if ($html instanceof \MUtil_Html_FormLayout) {
            $html->setAsFormLayout($this);
        } else {
            // Set this element as the form decorator
            $decorator = new \MUtil_Html_ElementDecorator();
            $decorator->setHtmlElement($html);
            // $decorator->setPrologue($formrep); // Renders hidden elements before this element
            $this->setDecorators(array($decorator, 'AutoFocus', 'Form'));
        }

        $this->_html_element = $html;

        return $this;
    }

    /**
     * Render the element labels with a fixed width
     *
     * @param mixed $width The style.width content for the labels
     * @return \MUtil_Form (continuation pattern)
     */
    public function setLabelWidth($width)
    {
        $this->_labelWidth = $width;

        $layout = new \MUtil_Html_DlElement();
        $layout->setAsFormLayout($this, $width, $this->getDisplayOrder());

        $this->_html_element = $layout;

        return $this;
    }

    /**
     * Render elements with an automatically calculated label width, by multiplying the maximum number of
     * characters in a label with this factor.
     *
     * @param float $factor To multiply the widest nummers of letters in the labels with to calculate the width in em at drawing time
     * @return \MUtil_Form (continuation pattern)
     */
    public function setLabelWidthFactor($factor)
    {
        $this->_labelWidthFactor = $factor;

        $layout = new \MUtil_Html_DlElement();
        $layout->setAutoWidthFormLayout($this, $factor, $this->getDisplayOrder());

        $this->_html_element = $layout;

        return $this;
    }

    /**
     * Is the form Lazy or can it be rendered normally?
     *
     * @param boolean $lazy
     */
    public function setLazy($lazy = false)
    {
        $this->_Lazy = (bool) $lazy;
    }

    /**
     * Set view object
     *
     * @param  \Zend_View_Interface $view
     * @return \Zend_Form
     */
    public function setView(\Zend_View_Interface $view = null)
    {
        if ($view) {
            if (! $this->_no_jquery) {
                $this->_activateJQueryView($view);
            }
        }

        return parent::setView($view);
    }

    /**
     *
     * @return boolean
     */
    public function usesBootstrap()
    {
        return ! $this->_no_bootstrap;
    }

    /**
     *
     * @return boolean
     */
    public function usesJQuery()
    {
        return ! $this->_no_jquery;
    }
}
