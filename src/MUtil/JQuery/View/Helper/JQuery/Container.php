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
 *
 *
 * @package    MUtil
 * @subpackage JQuery
 * @author     Jasper van Gestel <jappie@dse.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @version    $Id: Container.php 177 2014-02-27 12:53:30 matijsdejong $
 */

/**
 *
 * @package    MUtil
 * @subpackage JQuery
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.6.5
 */
class MUtil_JQuery_View_Helper_JQuery_Container extends \ZendX_JQuery_View_Helper_JQuery_Container
{
    /**
     * Render jQuery stylesheets
     *
     * @return string
     */
    public function renderStylesheets()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return $this->_renderStylesheets();
    }

    /**
     * Renders all javascript file related stuff of the jQuery enviroment.
     *
     * @return string
     */
    public function renderScriptTags()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return $this->_renderScriptTags();
    }

    /**
     * Renders all javascript code related stuff of the jQuery enviroment.
     *
     * @return string
     */
    public function renderExtras()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return $this->_renderExtras();
    }

    /**
     * String representation of jQuery javascript files and code
     * @return string
     */
    public function renderJavascript()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $html  = $this->_renderScriptTags() . PHP_EOL
               . $this->_renderExtras();
        return $html;
    }

    /**
     * Renders all javascript code related stuff of the jQuery enviroment.
     *
     * @return string
     */
    protected function _renderExtras()
    {
        $onLoadActions = array();
        if( ($this->getRenderMode() & ZendX_JQuery::RENDER_JQUERY_ON_LOAD) > 0) {
            foreach ($this->getOnLoadActions() as $callback) {
                $onLoadActions[] = $callback;
            }
        }

        $javascript = '';
        if( ($this->getRenderMode() & ZendX_JQuery::RENDER_JAVASCRIPT) > 0) {
            $javascript = implode("\n    ", $this->getJavascript());
        }

        $content = '';

        if (!empty($onLoadActions)) {
            if(true === ZendX_JQuery_View_Helper_JQuery::getNoConflictMode()) {
                $content .= '$j(document).ready(function() {'."\n    ";
            } else {
                $content .= '$(document).ready(function() {'."\n    ";
            }
            $content .= implode("\n    ", $onLoadActions) . "\n";
            $content .= '});'."\n";
        }

        if (!empty($javascript)) {
            $content .= $javascript . "\n";
        }

        if (preg_match('/^\s*$/s', $content)) {
            return '';
        }

        $nonceAttribute = $this->getNonceAttribute();

        $html = '<script type="text/javascript"'.$nonceAttribute.'>' . PHP_EOL
            . (($this->_isXhtml) ? '//<![CDATA[' : '//<!--') . PHP_EOL
            . $content
            . (($this->_isXhtml) ? '//]]>' : '//-->') . PHP_EOL
            . PHP_EOL . '</script>';
        return $html;
    }

    /**
     * Renders all javascript file related stuff of the jQuery enviroment.
     *
     * @return string
     */
    protected function _renderScriptTags()
    {
        $scriptTags = '';
        if( ($this->getRenderMode() & ZendX_JQuery::RENDER_LIBRARY) > 0) {
            $source = $this->_getJQueryLibraryPath();

            $nonceAttribute = $this->getNonceAttribute();

            $scriptTags .= '<script type="text/javascript" src="' . $source . '"'.$nonceAttribute.'></script>' . PHP_EOL;

            if($this->uiIsEnabled()) {
                $uiPath = $this->_getJQueryUiLibraryPath();
                $scriptTags .= '<script type="text/javascript" src="'.$uiPath.'"'.$nonceAttribute.'></script>' . PHP_EOL;
            }

            if(ZendX_JQuery_View_Helper_JQuery::getNoConflictMode() == true) {
                $scriptTags .= '<script type="text/javascript"'.$nonceAttribute.'>var $j = jQuery.noConflict();</script>' . PHP_EOL;
            }
        }

        if( ($this->getRenderMode() & ZendX_JQuery::RENDER_SOURCES) > 0) {
            foreach($this->getJavascriptFiles() AS $javascriptFile) {
                $scriptTags .= '<script type="text/javascript" src="' . $javascriptFile . '"'.$nonceAttribute.'></script>' . PHP_EOL;
            }
        }

        return $scriptTags;
    }

    /**
     * @return string html attribute value of script nonce set in Escort
     */
    protected function getNonceAttribute()
    {
        $nonce = '';
        if (\MUtil\Javascript::$scriptNonce) {
            $nonce = ' nonce="'.\MUtil\Javascript::$scriptNonce.'"';
        }
        return $nonce;
    }
}
