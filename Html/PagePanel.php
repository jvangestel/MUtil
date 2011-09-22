<?php


/**
 * Copyright (c) 2011, Erasmus MC
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * Neither the name of Erasmus MC nor the
 *      names of its contributors may be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class MUtil_Html_PagePanel extends MUtil_Html_Sequence implements MUtil_Lazy_Procrastinator
{
    protected $_baseUrl = array();

    protected $_currentPage;
    protected $_currentPageDefault = 1;
    protected $_currentPageParam   = 'page';

    protected $_defaultContent         = array();
    protected $_defaultDisabledContent = array('class' => 'disabled');
    protected $_defaultEnabledContent  = array();

    protected $_itemCount;
    protected $_itemCountDefault = 10;
    protected $_itemCountParam   = 'items';
    protected $_itemCountValues  = array(5, 10, 15, 20, 50, 100, 200, 500, 1000, 2000);

    protected $_lazy;

    protected $_pages;
    protected $_paginator;

    protected $_request;

    protected $_scrollingStyle = 'sliding';

    protected $_specialTypes = array(
        'Zend_Controller_Request_Abstract' => 'setRequest',
        'Zend_Paginator'                   => 'setPaginator',
        );

    public $pages;
    public $paginator;

    protected function _applyDefaults($condition, array $args)
    {
        // Apply default arguments
        $args = $args + $this->_defaultContent;

        foreach ($this->_defaultEnabledContent as $key => $content) {
            $other = isset($args[$key]) ? $args[$key] : null;
            if ($other instanceof MUtil_Html_AttributeInterface) {
                $other->add(MUtil_Lazy::iff($condition, $content));
            } else {
                $args[$key] = MUtil_Lazy::iff($condition, $content, $other);
            }
        }
        foreach ($this->_defaultDisabledContent as $key => $content) {
            $other = isset($args[$key]) ? $args[$key] : null;
            if ($other instanceof MUtil_Html_AttributeInterface) {
                $other->add(MUtil_Lazy::iff($condition, null, $content));
            } else {
                $args[$key] = MUtil_Lazy::iff($condition, $other, $content);
            }
        }

        return $args;
    }

    protected function _checkVariables($force = false)
    {
        if ($this->_paginator) {
            if ($force || $this->_currentPage || $this->_request) {
                $this->_paginator->setCurrentPageNumber($this->getCurrentPage());
            }
            if ($force || $this->_itemCount || $this->_request) {
                $this->_paginator->setItemCountPerPage($this->getItemCount());
            }
        }
    }

    protected function _createHref($param, $page)
    {
        return new MUtil_Html_HrefArrayAttribute(array($param => $page) + $this->_baseUrl);
    }

    public function createCountLink($condition, $count, array $args)
    {
        // Use the condition for the $href
        $element = MUtil_Html::create()->a(
            MUtil_Lazy::iff($condition, $this->_createHref($this->_itemCountParam, $count)),
            $this->_applyDefaults($condition, $args));

        // and make the tagName an if
        $element->tagName = MUtil_Lazy::iff($condition, 'a', 'span');

        return $element;
    }

    public function createPageLink($condition, $page, array $args)
    {
        // Use the condition for the $href
        $element = MUtil_Html::create()->a(
            MUtil_Lazy::iff($condition, $this->_createHref($this->_currentPageParam, $page)),
            $this->_applyDefaults($condition, $args));

        // and make the tagName an if
        $element->tagName = MUtil_Lazy::iff($condition, 'a', 'span');

        return $element;
    }

    public function firstPage($label = '<<', $args_array = null)
    {
        $args = MUtil_Ra::args(func_get_args());

        // Apply default
        if (! isset($args[0])) {
            $args[] = '<<';
        }

        return $this->createPageLink($this->pages->previous, $this->pages->first, $args);
    }

    protected function getCookieLocation()
    {
        $request = $this->getRequest();

        $front  = $front = Zend_Controller_Front::getInstance();
        $result = $request->getBasePath();

        $bname = $request->getModuleKey();
        if (isset($this->_baseUrl[$bname])) {
            $result .= '/' . $this->_baseUrl[$bname];
        } elseif (($val = $request->getModuleName()) && ($val != $front->getDefaultModule())) {
            $result .= '/' . $val;
        }

        $bname = $request->getControllerKey();
        if (isset($this->_baseUrl[$bname])) {
            $result .= '/' . $this->_baseUrl[$bname];
        } elseif (($val = $request->getControllerName())  && ($val != $front->getDefaultControllerName())) {
            $result .= '/' . $val;
        }

        $bname = $request->getActionKey();
        if (isset($this->_baseUrl[$bname])) {
            $result .= '/' . $this->_baseUrl[$bname];
        } elseif (($val = $request->getActionName())  && ($val != $front->getDefaultAction())) {
            $result .= '/' . $val;
        }

        return $result;
    }


    public function getCurrentPage()
    {
        if (null === $this->_currentPage) {
            $this->_currentPage = $this->getCurrentPageDefault();

            if ($param_name = $this->getCurrentPageParam()) {
                $request = $this->getRequest();

                if ($currentPage = $request->getParam($param_name)) {
                    $this->_currentPage = $currentPage;
                    // Set cookie
                } elseif ($request instanceof Zend_Controller_Request_Http) {
                    $this->_currentPage = $request->getCookie($param_name, $this->_currentPage);
                }
            }
        }

        return $this->_currentPage;
    }

    public function getCurrentPageDefault()
    {
        return $this->_currentPageDefault;
    }

    public function getCurrentPageParam()
    {
        return $this->_currentPageParam;
    }

    public function getItemCount()
    {
        if (null === $this->_itemCount) {
            $this->_itemCount = $this->getItemCountDefault();

            if ($param_name = $this->getItemCountParam()) {
                $request = $this->getRequest();

                if ($itemCount = $request->getParam($param_name)) {
                    $this->_itemCount = $itemCount;
                    setcookie($param_name, $itemCount, time() + (30 * 86400), $this->getCookieLocation());
                } elseif ($request instanceof Zend_Controller_Request_Http) {
                    $this->_itemCount = $request->getCookie($param_name, $this->_itemCount);
                }
            }
        }

        return $this->_itemCount;
    }

    public function getItemCountDefault()
    {
        return $this->_itemCountDefault;
    }

    public function getItemCountLess()
    {
        $pos = array_search($this->getItemCount(), $this->_itemCountValues);
        if ($pos || ($pos === 0)) {
            $pos--;

            if (isset($this->_itemCountValues[$pos])) {
                return $this->_itemCountValues[$pos];
            }
        }
    }

    public function getItemCountMax()
    {
        return max($this->_itemCountValues);
    }

    public function getItemCountMore()
    {
        $pos = array_search($this->getItemCount(), $this->_itemCountValues);
        if ($pos || ($pos === 0)) {
            $pos++;

            if (isset($this->_itemCountValues[$pos])) {
                return $this->_itemCountValues[$pos];
            }
        }
    }

    public function getItemCountNotMax()
    {
        return $this->getItemCount() != $this->getItemCountMax();
    }

    public function getItemCountParam()
    {
        return $this->_itemCountParam;
    }

    public function getPages($scrollingStyle = null)
    {
        if (null === $scrollingStyle) {
            $scrollingStyle = $this->_scrollingStyle;
        }

        if ((! $this->_pages) || ($scrollingStyle != $this->_scrollingStyle)) {
            $this->_pages = $this->_paginator->getPages($scrollingStyle);
            $this->_scrollingStyle = $scrollingStyle;
        }

        return $this->_pages;
    }

    public function getPaginator()
    {
        return $this->_paginator;
    }

    /**
     * Return the Request object
     *
     * @return Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        if (! $this->_request) {
            $front = Zend_Controller_Front::getInstance();
            $this->setRequest($front->getRequest());
        }

        return $this->_request;
    }

    public function getScrollingStyle()
    {
        return $this->_scrollingStyle;
    }

    protected function init()
    {
        parent::init();

        $this->paginator = $this->toLazy()->getPaginator();
        $this->pages     = $this->toLazy()->getPages();
    }

    public function lastPage($label = '>>', $args_array = null)
    {
        $args = MUtil_Ra::args(func_get_args());

        // Apply default
        if (! isset($args[0])) {
            $args[] = '>>';
        }

        return $this->createPageLink($this->pages->next, $this->pages->last, $args);
    }

    public function nextPage($label = '>', $args_array = null)
    {
        $args = MUtil_Ra::args(func_get_args());

        // Apply default
        if (! isset($args[0])) {
            $args[] = '>';
        }

        return $this->createPageLink($this->pages->next, $this->pages->next, $args);
    }

    public function pageLinks($first = '<<', $previous = '<', $next = '>', $last = '>>', $glue = ' ', $args = null)
    {
        $argDefaults = array('first' => '<<', 'previous' => '<', 'next' => '>', 'last' => '>>', 'glue' => ' ');
        $argNames    = array_keys($argDefaults);

        $args = MUtil_Ra::args(func_get_args(), $argNames, $argDefaults);

        foreach ($argNames as $name) {
            $$name = $args[$name];
            unset($args[$name]);
        }

        $div = MUtil_Html::create()->sequence(array('glue' => $glue));

        if ($first) { // Can be null or array()
            $div[] = $this->firstPage((array) $first + $args);
        }
        if ($previous) { // Can be null or array()
            $div[] = $this->previousPage((array) $previous + $args);
        }
        $div[] = $this->rangePages($glue, $args);
        if ($next) { // Can be null or array()
            $div[] = $this->nextPage((array) $next + $args);
        }
        if ($last) { // Can be null or array()
            $div[] = $this->lastPage((array) $last + $args);
        }

        return MUtil_Lazy::iff(MUtil_Lazy::comp($this->pages->pageCount, '>', 1), $div);
    }

    public static function pagePanel($paginator = null, $request = null, $args = null)
    {
        $args = func_get_args();

        $pager = new self($args);

        $pager[] = $pager->pageLinks();
        $pager->div($pager->uptoOffDynamic(), array('style' => 'float: right;'));

        return $pager;
    }

    public function previousPage($label = '<', $args_array = null)
    {
        $args = MUtil_Ra::args(func_get_args());

        // Apply default
        if (! isset($args[0])) {
            $args[] = '<';
        }

        return $this->createPageLink($this->pages->previous, $this->pages->previous, $args);
    }

    public function rangePages($glue = ' ', $args_array = null)
    {
        $args = MUtil_Ra::args(func_get_args(), array('glue'), array('glue' => ' '));

        return new MUtil_Html_PageRangeRenderer($this, $args);
    }

    public function setBaseUrl(array $baseUrl = null)
    {
        $this->_baseUrl = (array) $baseUrl;
        return $this;
    }

    public function setCurrentPage($currentPage)
    {
        $this->_currentPage = $currentPage;
        $this->_checkVariables();

        return $this;
    }

    public function setCurrentPageDefault($currentPageDefault)
    {
        $this->_currentPageDefault = $currentPageDefault;
        return $this;
    }

    public function setCurrentPageParam($currentPageParam)
    {
        $this->_currentPageParam = $currentPageParam;
        return $this;
    }

    public function setItemCount($itemCount)
    {
        $this->_itemCount = $itemCount;
        $this->_checkVariables();

        return $this;
    }

    public function setItemCountDefault($itemCountDefault)
    {
        $this->_itemCountDefault = $itemCountDefault;
        return $this;
    }

    public function setItemCountParam($itemCountParam)
    {
        $this->_itemCountParam = $itemCountParam;
        return $this;
    }

    public function setPaginator(Zend_Paginator $paginator)
    {
        $this->_paginator = $paginator;

        if ($this->view) {
            $this->_paginator->setView($this->view);
        }
        $this->_checkVariables();

        return $this;
    }

    /**
     * Set the Request object
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return Zend_Controller_Action
     */
    public function setRequest(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
        $this->_checkVariables();

        return $this;
    }

    public function setScrollingStyle($scrollingStyle)
    {
        $this->_scrollingStyle = $scrollingStyle;
        return $this;
    }

    /**
     * Set the View object
     *
     * @param  Zend_View_Interface $view
     * @return Zend_View_Helper_Abstract
     */
    public function setView(Zend_View_Interface $view)
    {
        if ($this->_paginator) {
            $this->_paginator->setView($view);
        }

        return parent::setView($view);
    }

    public function toLazy()
    {
        if (! $this->_lazy) {
            $this->_lazy = new MUtil_Lazy_ObjectWrap($this);
        }

        return $this->_lazy;
    }

    public function uptoOff($upto = '-', $off = '/', $glue = ' ')
    {
        $seq = new MUtil_Html_Sequence();
        $seq->setGlue($glue);
        $seq->if($this->pages->totalItemCount, $this->pages->firstItemNumber, 0);
        $seq[] = $upto;
        $seq[] = $this->pages->lastItemNumber;
        $seq[] = $off;
        $seq[] = $this->pages->totalItemCount;

        return $seq;
    }

    public function uptoOffDynamic($upto = '~', $off = '/', $less = '-', $more = '+', $all = null, $glue = ' ', $args = null)
    {
        $argDefaults = array('upto' => '~', 'off' => '/', 'less' => '-', 'more' => '+', 'all' => null, 'glue' => ' ');
        $argNames    = array_keys($argDefaults);

        $args = MUtil_Ra::args(func_get_args(), $argNames, $argDefaults);

        foreach ($argNames as $name) {
            $$name = $args[$name];
            unset($args[$name]);
        }

        $seq = new MUtil_Html_Sequence();
        $seq->setGlue($glue);
        if (null !== $upto) {
            $seq->if($this->pages->totalItemCount, $this->pages->firstItemNumber, 0);
            $seq[] = $upto;
        }
        if (null !== $less) {
            $cless = $this->toLazy()->getItemCountLess();
            $seq[] = $this->createCountLink($cless, $cless, (array) $less + $args);
        }
        if (null !== $upto) {
            $seq[] = $this->pages->lastItemNumber;
        }
        if (null !== $more) {
            $cmore = $this->toLazy()->getItemCountMore();
            $seq[] = $this->createCountLink($cmore, $cmore, (array) $more + $args);
        }
        if (null !== $all) {
            $seq[] = $this->createCountLink($this->toLazy()->getItemCountNotMax(), $this->toLazy()->getItemCountMax(), (array) $all + $args);
        }
        if (null !== $off) {
            if (null !== $upto) {
                $seq[] = $off;
            }
            $seq[] = $this->pages->totalItemCount;
        }

        return $seq;
    }
}