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
 * @package    MUtil
 * @subpackage Snippets
 * @author     Menoo Dekker <menno.dekker@erasmusmc.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @version    $Id$
 */

/**
 * Handles loading of snippets
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5.5
 */
interface MUtil_Snippets_SnippetLoaderInterface
{
    /**
     * Sets the source of variables and the first directory for snippets
     *
     * @param mixed $source Something that is or can be made into \MUtil_Registry_SourceInterface, otheriwse \Zend_Registry is used.
     * @param array $dirs prefix => pathname The inital paths to load from
     */
    public function __construct($source = null, array $dirs = array());

    /**
     * Add prefixed paths to the registry of paths
     *
     * @param string $prefix
     * @param string $path
     * @return \MUtil_Snippets_SnippetLoaderInterface
     */
    public function addPrefixPath($prefix, $path);

    /**
     * Searches and loads a .php snippet file.
     *
     * @param string $className The name of the snippet
     * @param array $extraSourceParameters name/value pairs to add to the source for this snippet
     * @return \MUtil_Snippets_SnippetInterface The snippet
     */
    public function getSnippet($className, array $extraSourceParameters = null);

    /**
     * Returns a source of values for snippets.
     *
     * @return \MUtil_Registry_SourceInterface
     */
    public function getSource();

    /**
     * Remove a prefix (or prefixed-path) from the registry
     *
     * @param string $prefix
     * @param string $path OPTIONAL
     * @return \MUtil_Snippets_SnippetLoaderInterface
     */
    public function removePrefixPath($prefix, $path = null);

    /**
     * Sets the source of variables for snippets
     *
     * @param \MUtil_Registry_SourceInterface $source
     * @return \MUtil_Snippets_SnippetLoader (continuation pattern)
     */
    public function setSource(\MUtil_Registry_SourceInterface $source);
}