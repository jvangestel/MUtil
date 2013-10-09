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
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @version    $Id$
 */

/**
 * A model combines knowedge about a set of data with knowledge required to manipulate
 * that set of data. I.e. it can store data about fields such as type, label, length,
 * etc... and meta data about the object like the current query filter and sort order,
 * with manipulation methods like save(), load(), loadNew() and delete().
 *
 * @see MUtil_Model_ModelAbstract
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class MUtil_Model
{
    /**
     * Indentifier for form (meta) assemblers and (field) processors
     */
    const FORM = 'form';

    /**
     * Indentifier for assemblers meta key
     */
    const META_ASSEMBLERS = 'assemblers';

    /**
     * In order to keep the url's short and to hide any field names from
     * the user, model identifies key values by using 'id' for a single
     * key value and id1, id2, etc... for multiple keys.
     */
    const REQUEST_ID = 'id';

    /**
     * Helper constant for first key value in multi value key.
     */
    const REQUEST_ID1 = 'id1';

    /**
     * Helper constant for second key value in multi value key.
     */
    const REQUEST_ID2 = 'id2';

    /**
     * Helper constant for third key value in multi value key.
     */
    const REQUEST_ID3 = 'id3';

    /**
     * Helper constant for forth key value in multi value key.
     */
    const REQUEST_ID4 = 'id4';

    /**
     * Default parameter name for sorting ascending.
     */
    const SORT_ASC_PARAM  = 'asort';

    /**
     * Default parameter name for sorting descending.
     */
    const SORT_DESC_PARAM = 'dsort';

    /**
     * Default parameter name for wildcard text search.
     */
    const TEXT_FILTER = 'search';

    /**
     * Type identifiers for calculated fields
     */
    const TYPE_NOVALUE      = 0;

    /**
     * Type identifiers for string fields, default type
     */
    const TYPE_STRING       = 1;

    /**
     * Type identifiers for numeric fields
     */
    const TYPE_NUMERIC      = 2;

    /**
     * Type identifiers for date fields
     */
    const TYPE_DATE         = 3;

    /**
     * Type identifiers for date time fields
     */
    const TYPE_DATETIME     = 4;

    /**
     * Type identifiers for time fields
     */
    const TYPE_TIME         = 5;

    /**
     * Type identifiers for sub models that can return multiple row per item
     */
    const TYPE_CHILD_MODEL  = 6;

    /**
     *
     * @var array of MUtil_Loader_PluginLoader
     */
    private static $_loaders = array();

    /**
     *
     * @var array of global for directory paths
     */
    private static $_nameSpaces = array('MUtil');

    /**
     * Static variable for debuggging purposes. Toggles the echoing of e.g. of sql
     * select statements, using MUtil_Echo.
     *
     * Implemention classes can use this variable to determine whether to display
     * extra debugging information or not. Please be considerate in what you display:
     * be as succint as possible.
     *
     * Use:
     *     MUtil_Model::$verbose = true;
     * to enable.
     *
     * @var boolean $verbose If true echo retrieval statements.
     */
    public static $verbose = false;

    /**
     * Returns the plugin loader for assemblers
     *
     * @return MUtil_Loader_PluginLoader
     */
    public static function getAssemblerLoader()
    {
        return self::getLoader('Assembler');
        // maybe add interface def to plugin loader: MUtil_Model_AssemblerInterface
    }

    /**
     * Returns a subClass plugin loader
     *
     * @param string $prefix The prefix to load the loader for. CamelCase and should not contain an '_', '/' or '\'.
     * @return MUtil_Loader_PluginLoader
     */
    public static function getLoader($subClass)
    {
        if (! isset(self::$_loaders[$subClass])) {
            $loader = new MUtil_Loader_PluginLoader();

            foreach (self::$_nameSpaces as $nameSpace) {
                $loader->addPrefixPath(
                        $nameSpace . '_Model_' . $subClass,
                        $nameSpace . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . $subClass);
            }
            $loader->addFallBackPath();

            self::$_loaders[$subClass] = $loader;
        }

        return self::$_loaders[$subClass];
    }

    /**
     * Returns the plugin loader for processors.
     *
     * @return MUtil_Loader_PluginLoader
     */
    public static function getProcessorLoader()
    {
        return self::getLoader('Processor');
        // maybe add interface def to plugin loader: MUtil_Model_AssemblerInterface
    }

    /**
     * Sets the plugin loader for assemblers
     *
     * @param MUtil_Loader_PluginLoader $loader
     */
    public static function setAssemblerLoader(MUtil_Loader_PluginLoader $loader)
    {
        self::setLoader($loader, 'Assembler');
    }

    /**
     * Sets the plugin loader for assemblers
     *
     * @param MUtil_Loader_PluginLoader $loader
     */
    public static function setLoader(MUtil_Loader_PluginLoader $loader, $subClass)
    {
        self::$_loaders[$subClass] = $loader;
    }

    /**
     * Sets the plugin loader for processors.
     *
     * @param MUtil_Loader_PluginLoader $loader
     */
    public static function setProcessorLoader(MUtil_Loader_PluginLoader $loader)
    {
        self::setLoader($loader, 'Processor');
    }
}
