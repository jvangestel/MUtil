<?php

/**
 *
 * @package    MUtil
 * @subpackage File
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

/**
 * File system utility functions
 *
 * @package    MUtil
 * @subpackage File
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.2
 */
class MUtil_File
{
    /**
     * Make sure the filename is actually allowd by underlying file systems
     *
     * @param string $filename
     * @return string
     */
    public static function cleanupName($filename)
    {
        return preg_replace('/[\|\\\?\*<\":>\+\[\]\/]\x00/', "_", $filename);
    }

    /**
     * Make sure the slashes point the way of the underlying server only
     *
     * @param string $filename
     * @return string
     */
    public static function cleanupSlashes($filename)
    {
        return str_replace('\\' === DIRECTORY_SEPARATOR ? '/' : '\\', DIRECTORY_SEPARATOR, $filename);
    }

    /**
     * Creates a temporary empty file in the directory but first cleans
     * up any files older than $keepFor in that directory.
     *
     * When no directory is used sys_get_temp_dir() is used and no cleanup is performed.
     *
     * @param string $dir The directory for the files
     * @param string $prefix Optional prefix
     * @param int $keepFor The number of second a file is kept, use 0 for always
     * @return string The name of the temporary file
     */
    public static function createTemporaryIn($dir = null, $prefix = null, $keepFor = 86400)
    {
        $filename = self::getTemporaryIn($dir, $prefix, $keepFor);

        file_put_contents($filename, '');

        // \MUtil_Echo::track($filename, file_exists($filename), filesize($filename));

        return $filename;
    }

    /**
     * Ensure the directory does really exist or throw an exception othewise
     *
     * @param string $dir The path of the directory
     * @param int $mode Unix file mask mode, ignored on Windows
     * @return string the directory
     * @throws \Zend_Exception
     */
    public static function ensureDir($dir, $mode = 0777)
    {
        // Clean up directory name
        $dir = self::cleanupSlashes($dir);

        // Cascade to ensure parent dir creation (not all operating systems allow cascaded creation)
        $parent = dirname($dir);
        if (strlen($parent) > 1 && (! is_dir($parent))) {
            self::ensureDir($parent, $mode);
        }
        if (! is_dir($dir)) {
            if (! @mkdir($dir, $mode, true)) {
                throw new \Zend_Exception(sprintf(
                        "Could not create '%s' directory: %s.",
                        $dir,
                        \MUtil_Error::getLastPhpErrorMessage('reason unknown')
                        ));
            }
        }

        return $dir;
    }

    /**
     * Format in drive units
     *
     * @param int $size
     * @return string
     */
    public static function getByteSized($size)
    {
        $units = array( '', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return \Zend_Locale_Format::toInteger($size / pow(1024, $power)) . ' ' . $units[$power];
    }

    /**
     * Returns an array containing all the files (not directories) in a
     * recursive directory listing from $dir.
     *
     * @param string $dir
     * @return array
     */
    public static function getFilesRecursive($dir)
    {
        $results = array();

        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    if (('.' !== $file) && ('..' !== $file)) {
                        $results = array_merge($results, self::getFilesRecursive($path));
                    }
                } else {
                    $results[] = $path;
                }
            }
        }

        return $results;
    }

    /**
     * Creates a temporary filename in the directory but first cleans
     * up any files older than $keepFor in that directory.
     *
     * When no directory is used sys_get_temp_dir() is used and no cleanup is performed.
     *
     * @param string $dir The directory for the files
     * @param string $prefix Optional prefix
     * @param int $keepFor The number of second a file is kept, use 0 for always
     * @return string The name of the temporary file
     */
    public static function getTemporaryIn($dir = null, $prefix = null, $keepFor = 86400)
    {
        if (null === $dir) {
            $output = tempnam(sys_get_temp_dir(), $prefix);
        } else {

            self::ensureDir($dir);

            if ($keepFor) {
                // Clean up old temporaries
                foreach (glob($dir . '/*', GLOB_NOSORT) as $filename) {
                    if ((!is_dir($filename)) && (filemtime($filename) + $keepFor < time())) {
                        @unlink($filename);
                    }
                }
            }

            $output = tempnam($dir, $prefix);
        }
        chmod($output, 0777);

        return $output;
    }

    /**
     * Is the path a rooted path?
     * On Windows this does not require a drive letter, but one is allowed
     *
     * Check OS specific plus check for urls
     *
     * @param string $path
     * @return boolean
     */
    public static function isRootPath($path)
    {
        if (! $path) {
            return false;
        }
        // Quick checkes first and then something just in case
        if (('\\' == $path[0]) || ('/' == $path[0]) || \MUtil_String::startsWith($path, DIRECTORY_SEPARATOR)) {
            return true;
        }
        // One more check for windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return preg_match('#^[a-zA-Z]:\\\\#', $path);
        }
        // The check for uri's (frp, http, https
        return preg_match('#^[a-zA-Z]+://#', $path);
    }

    /**
     * Returns true when running on Windows machine
     *
     * @return boolean
     */
    public static function isOnWindows()
    {
        return (DIRECTORY_SEPARATOR == '\\');
    }

    /**
     * Removes the c: part from the filename
     *
     * @param string $path
     * @return string
     */
    public static function removeWindowsDriveLetter($path)
    {
        return preg_replace('/^([a-zA-Z]:)/', '', $path);
    }
}
