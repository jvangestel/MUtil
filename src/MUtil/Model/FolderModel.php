<?php

/**
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 201e Erasmus MC
 * @license    New BSD License
 */

/**
 * A model for listing files in directory structures
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.3
 */
class MUtil_Model_FolderModel extends \MUtil_Model_ArrayModelAbstract
{
    /**
     * The directory to list
     *
     * @var string
     */
    protected $dir;

    /**
     * @var array The extensions allowed
     */
    protected $extensions;
    
    /**
     * Regex filename mask, use of / slashes for directory seperator required
     *
     * @var string
     */
    protected $mask;

    /**
     * When true searches directories recursively
     *
     * @var boolean
     */
    protected $recursive;

    /**
     * When true also follows symlinks. Only works when recursive is true
     *
     * @var boolean
     */
    protected $followSymlinks;

    /**
     *
     * @param string  $dir The (start) directory
     * @param mixed   $extensionsOrMask An optional array of extensions or a regex file mask, use of / for directory separator required
     * @param boolean $recursive When true the directory is searched recursively
     * @param boolean $followSymlinks When true symlinks are folloed
     */
    public function __construct($dir, $extensionsOrMask = null, $recursive = false, $followSymlinks = false)
    {
        parent::__construct($dir);

        $this->dir = $dir;

        if (is_array($extensionsOrMask)) {
            $this->extensions = array_unique(\MUtil_Ra::flatten($extensionsOrMask), SORT_STRING);
            $this->mask = \MUtil_File::createMask($extensionsOrMask);
        } else {
            $this->mask = $extensionsOrMask;
        }

        $this->recursive = $recursive;

        $this->followSymlinks = $followSymlinks;

        $this->set('fullpath',     'type', \MUtil_Model::TYPE_STRING);
        $this->set('path',         'type', \MUtil_Model::TYPE_STRING);
        $this->set('filename',     'type', \MUtil_Model::TYPE_STRING);
        $this->set('relpath',      'type', \MUtil_Model::TYPE_STRING);
        $this->set('urlpath',      'type', \MUtil_Model::TYPE_STRING);
        $this->set('extension',    'type', \MUtil_Model::TYPE_STRING);
        $this->set('content',      'type', \MUtil_Model::TYPE_STRING);
        $this->set('size',         'type', \MUtil_Model::TYPE_NUMERIC);
        $this->set('changed',      'type', \MUtil_Model::TYPE_DATETIME);

        $this->setKeys(array('urlpath'));
    }

    /**
     * An ArrayModel assumes that (usually) all data needs to be loaded before any load
     * action, this is done using the iterator returned by this function.
     *
     * @return \Traversable Return an iterator over or an array of all the rows in this object
     */
    protected function _loadAllTraversable()
    {
        if (! is_dir($this->dir)) {
            return array();
        }

        if ($this->recursive) {
            $directoryIteratorFlags = \FilesystemIterator::CURRENT_AS_FILEINFO;
            if ($this->followSymlinks) {
                $directoryIteratorFlags = \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::FOLLOW_SYMLINKS;
            }

            $dirIter = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($this->dir, $directoryIteratorFlags),
                    \RecursiveIteratorIterator::SELF_FIRST,
                    \RecursiveIteratorIterator::CATCH_GET_CHILD
                    );
        } else {
            $dirIter = new \FilesystemIterator($this->dir, \FilesystemIterator::CURRENT_AS_FILEINFO);
        }

        $modelIter = new \MUtil_Model_Iterator_FolderModelIterator($dirIter, $this->dir, $this->mask);

        return $modelIter;
    }

    /**
     * Delete items from the model
     *
     * @param mixed $filter True to use the stored filter, array to specify a different filter
     * @return int The number of items deleted
     */
    public function delete($filter = true)
    {
        $realFilter = $this->_checkFilterUsed($filter);

        // Disable delete all
        if (! $realFilter) {
            return 0;
        }

        $count = 0;
        foreach ($deleteable as $fileData) {
            if (unlink($fileData['fullpath'])) {
                $count = $count + 1;
            } elseif (file_exists($fileData['fullpath'])) {
                throw new \MUtil_Model_ModelException(sprintf(
                        'Unable to delete %s: %s',
                        $fileData['fullpath'],
                        \MUtil_Error::getLastPhpErrorMessage('reason unknown')
                        ));
            }
        }
    }

    /**
     * @return string The current directory used by this model
     */
    public function getCurrentDir()
    {
        return $this->dir;
    }
    
    /**
     * @return array The extensions allowed in this model
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Save a single model item.
     *
     * @param array $newValues The values to store for a single model item.
     * @param array $filter If the filter contains old key values these are used
     * to decide on update versus insert.
     * @return array The values as they are after saving (they may change).
     */
    public function save(array $newValues, array $filter = null)
    {
        $filename = false;
        if ($this->recursive) {
            if (isset($newValues['relpath'])) {
                $filename = $newValues['relpath'];
            }
        }

        if (!$filename && isset($newValues['filename'])) {
            $filename = $newValues['filename'];
        }

        if (! $filename) {
            throw new \MUtil_Model_ModelException('Cannot save file: no filename known');
        }

        $filename = trim($filename, '\\/');

        if ($this->dir) {
            $filename = $this->dir . DIRECTORY_SEPARATOR . $filename;
        }

        $content = isset($newValues['content']) ? $newValues['content'] : '';

        \MUtil_File::ensureDir(dirname($filename));

        if (false === file_put_contents($filename, $content) || (!file_exists($filename))) {
            throw new \MUtil_Model_ModelException(sprintf(
                    'Unable to save %s: %s',
                    $filename,
                    \MUtil_Error::getLastPhpErrorMessage('reason unknown')
                    ));
        }
        $this->setChanged(1);

        return $newValues;
    }
}
