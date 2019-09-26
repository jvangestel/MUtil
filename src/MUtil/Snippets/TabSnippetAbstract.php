<?php

/**
 *
 * @package    MUtil
 * @subpackage Snippets
 * @author     Menoo Dekker <menno.dekker@erasmusmc.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * Abstract class for quickly creating a tabbed bar, or rather a div that contains a number
 * of links, adding specific classes for display.
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
abstract class MUtil_Snippets_TabSnippetAbstract extends \MUtil_Snippets_SnippetAbstract
{
    /**
     * Optional standard url parts
     *
     * @var array
     */
    protected $baseUrl = array();

    /**
     * Shortfix to add class attribute
     *
     * @var string
     */
    protected $class = 'tabrow nav nav-tabs';

    /**
     *
     * @var string Id of the current tab
     */
    protected $currentTab;

    /**
     *
     * @var string Id of default tab
     */
    protected $defaultTab;

    /**
     * Show bar when there is only a single tab
     *
     * @var boolean
     */
    protected $displaySingleTab = false;

    /**
     * Default href parameter values
     *
     * @var array
     */
    protected $href = array();

    /**
     * Optional: $request or $tokenData must be set
     *
     * @var \Zend_Controller_Request_Abstract
     */
    protected $request;

    /**
     *
     * @var string Class attribute for active tab
     */
    protected $tabActiveClass = 'active';

    /**
     *
     * @var string Class attribute for all tabs
     */
    protected $tabClass       = 'tab';

    /**
     * Sets the default and current tab and returns the current
     *
     * @return The current tab
     */
    public function getCurrentTab()
    {
        $tabs = $this->getTabs();

        // When empty, first is default
        if (null === $this->defaultTab) {
            reset($tabs);
            $this->defaultTab = key($tabs);
        }
        if (null === $this->currentTab) {
            $this->currentTab = $this->request->getParam($this->getParameterKey());
        }

        // Param can exist and be empty or can have a false value
        if (! ($this->currentTab && isset($tabs[$this->currentTab])))  {
            $this->currentTab = $this->defaultTab;
        }

        return $this->currentTab;
    }

    /**
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @param \Zend_View_Abstract $view Just in case it is needed here
     * @return \MUtil_Html_HtmlInterface Something that can be rendered
     */
    public function getHtmlOutput(\Zend_View_Abstract $view)
    {
        $tabs = $this->getTabs();

        if ($tabs && ($this->displaySingleTab || count($tabs) > 1)) {
            // Set the correct parameters
            $this->getCurrentTab($tabs);

            // Let loose
            if (is_array($this->baseUrl)) {
                $this->href = $this->href + $this->baseUrl;
            }

            if (\MUtil_Bootstrap::enabled()) {
                $tabRow = \MUtil_Html::create()->ul();

                foreach ($tabs as $tabId => $content) {

                    $li = $tabRow->li(array('class' => $this->tabClass));

                    $li->a($this->getParameterKeysFor($tabId) + $this->href, $content);

                    if ($this->currentTab == $tabId) {
                        $li->appendAttrib('class', $this->tabActiveClass);
                    }
                }
            } else {
                $tabRow = \MUtil_Html::create()->div();

                foreach ($tabs as $tabId => $content) {

                    $div = $tabRow->div(array('class' => $this->tabClass));

                    $div->a($this->getParameterKeysFor($tabId) + $this->href, $content);

                    if ($this->currentTab == $tabId) {
                        $div->appendAttrib('class', $this->tabActiveClass);
                    }
                }
            }

            return $tabRow;
        } else {
            return null;
        }
    }

    /**
     * Return optionally the single parameter key which should left out for the default value,
     * but is added for all other tabs.
     *
     * @return mixed
     */
    protected function getParameterKey()
    {
        return null;
    }

    /**
     * Return the parameters that should be used for this tabId
     *
     * @param string $tabId
     * @return array
     */
    protected function getParameterKeysFor($tabId)
    {
        $paramKey = $this->getParameterKey();

        if ($paramKey) {
            return array($paramKey => $tabId);
        }

        return array();
    }

    /**
     * Function used to fill the tab bar
     *
     * @return array tabId => label
     */
    abstract protected function getTabs();
}
