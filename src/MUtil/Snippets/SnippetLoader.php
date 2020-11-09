<?php

/**
 *
 * @package    MUtil
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * This class handles the loading and processing of snippets.
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.4
 */
class MUtil_Snippets_SnippetLoader implements \MUtil_Snippets_SnippetLoaderInterface
{
    /**
     *
     * @var \MUtil_Loader_PluginLoader
     */
    protected $loader;

    /**
     * The file locations where to look for snippets.
     *
     * Can be overruled in descendants
     *
     * @var array
     */
    protected $snippetsDirectories;

    /**
     * The information source for snippets.
     *
     * @var \MUtil_Registry_SourceInterface
     */
    protected $snippetsSource;

    /**
     * Sets the source of variables and the first directory for snippets
     *
     * @param mixed $source Something that is or can be made into \MUtil_Registry_SourceInterface, otheriwse \Zend_Registry is used.
     * @param array $dirs prefix => pathname The inital paths to load from
     */
    public function __construct($source = null, array $dirs = array())
    {
        if (! $source instanceof \MUtil_Registry_Source) {
            $source = new \MUtil_Registry_Source($source);
        }
        $this->setSource($source);
        $this->loader = new \MUtil_Loader_PluginLoader($dirs);
        $this->loader->addPrefixPath('MUtil_Snippets_Standard', dirname(__FILE__) . '/Standard');
    }

    /**
     * Add prefixed paths to the registry of paths
     *
     * @param string $prefix
     * @param string $path
     * @return \MUtil_Snippets_SnippetLoaderInterface
     */
    public function addPrefixPath($prefix, $path, $prepend = true)
    {
        $this->loader->addPrefixPath($prefix, $path, $prepend);

        return $this;
    }

    /**
     * Searches and loads a .php snippet file.
     *
     * @param string $className The name of the snippet
     * @param array $extraSourceParameters name/value pairs to add to the source for this snippet
     * @return \MUtil_Snippets_SnippetInterface The snippet
     */
    public function getSnippet($className, array $extraSourceParameters = null)
    {
        $className = $this->loader->load($className);

        $snippet = new $className();

        if ($snippet instanceof \MUtil_Snippets_SnippetInterface) {
            // Add extra parameters when specified
            if ($extraSourceParameters) {
                $this->snippetsSource->addRegistryContainer($extraSourceParameters, 'tmpContainer');
            }

            if ($this->snippetsSource->applySource($snippet)) {
                if ($extraSourceParameters) {
                    // Can remove now, it was applied
                    $this->snippetsSource->removeRegistryContainer('tmpContainer');
                }

                return $snippet;

            } else {
                throw new \Zend_Exception("Not all parameters set for html snippet: '$className'. \n\nRequested variables were: " . implode(", ", $snippet->getRegistryRequests()));
            }
        } else {
            throw new \Zend_Exception("The snippet: '$className' does not implement the \MUtil_Snippets_SnippetInterface interface.");
        }
    }

    /**
     * Returns a source of values for snippets.
     *
     * @return \MUtil_Registry_SourceInterface
     */
    public function getSource()
    {
        return $this->snippetsSource;
    }

    /**
     * Remove a prefix (or prefixed-path) from the registry
     *
     * @param string $prefix
     * @param string $path OPTIONAL
     * @return \MUtil_Snippets_SnippetLoaderInterface
     */
    public function removePrefixPath($prefix, $path = null)
    {
        $this->loader->removePrefixPath($prefix, $path);

        return $this;
    }

    /**
     * Sets the source of variables for snippets
     *
     * @param \MUtil_Registry_SourceInterface $source
     * @return \MUtil_Snippets_SnippetLoader (continuation pattern)
     */
    public function setSource(\MUtil_Registry_SourceInterface $source)
    {
        $this->snippetsSource = $source;

        return $this;
    }
}
