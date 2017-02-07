<?php

/**
 *
 * @package    MUtil
 * @subpackage String
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

/**
 * A collection of static string utility functions
 *
 * @package    MUtil
 * @subpackage String
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class MUtil_String
{
    /**
     * Return the part of $input before any of the $charFilter characters
     *
     * beforeChars('abcdef', 'dgi') => 'abc'
     *
     * beforeChars('abcdef', 'xyz') => 'abcdef'
     *
     * @param string $input The text to return part of
     * @param string $charFilter The characters to filter on
     * @return string
     */
    public static function beforeChars($input, $charFilter)
    {
        $rest = strpbrk($input, $charFilter);

        if ($rest === false) {
            return $input;
        }

        return substr($input, 0, -strlen($rest));
    }

    /**
     * Returns true if needle is anywhere in haystack
     *
     * @param string $haystack The string to search in
     * @param string $needle The string to search for
     * @param boolean $caseInSensitive When true a case insensitive compare is performed
     * @return boolean
     */
    public static function contains($haystack, $needle, $caseInSensitive = false)
    {
        if ($caseInSensitive) {
            return stripos($haystack, $needle) !== false;
        }
        return strpos($haystack, $needle) !== false;
    }

    /**
     * Returns true if haystack ends with needle or needle is empty
     *
     * @param string $haystack The string to search in
     * @param string $needle The string to search for
     * @param boolean $caseInSensitive When true a case insensitive compare is performed
     * @return boolean
     */
    public static function endsWith($haystack, $needle, $caseInSensitive = false)
    {
        $len = strlen($needle);
        if ($len == 0) {
            return true;
        }

        if ((strlen($haystack) < $len)) {
            return false;
        }

        if ($caseInSensitive) {
            return strtolower(substr($haystack, -$len)) === strtolower($needle);
        }

        return substr($haystack, -$len) === (string) $needle;
    }

    /**
     * Test if a string is a valid base64 string.
     *
     * This test is only performed based on character inputand
     * does perform an actual decoding to be sure.
     *
     * @param string $input
     * @return boolean
     */
    public static function isBase64($input)
    {
        if (0 === (strlen($input) % 4)) {
            if (preg_match('/^[A-Za-z0-9+\\/]{2,}={0,2}$/', $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Split a string whereever the callback returns true (including
     * the character that returns true.
     *
     * \MUtil_String::splitOnCharCallback('abcDef', 'ctype_upper') => array(0 => 'abc', 1 => 'Def');
     *
     * \MUtil_String::splitOnCharCallback('abCDef', 'ctype_upper', true) => array(0 => 'ab', 2 => 'ef');
     *
     * @param string $input
     * @param callback $callBack Taking a single character as input
     * @param boolean $excludeDelimiter When excluded and 2 delimiters are next to each other, the output
     *                                  index in the array will skip a value
     * @return array index => split portion
     */
    public static function splitOnCharCallback($input, $callBack, $excludeDelimiter = false)
    {
        $current = 0;
        $length  = strlen($input);
        $results = array();

        for ($i = 0; $i < $length; $i++) {
            if ($callBack($input[$i])) {
                $current++;

                if ($excludeDelimiter) {
                    continue;
                }
            }
            if (isset($results[$current])) {
                $results[$current] .= $input[$i];
            } else {
                $results[$current] = $input[$i];
            }
        }

        return $results;
    }

    /**
     * Returns true if haystack starts with needle or needle is empty
     *
     * @param string $haystack The string to search in
     * @param string $needle The string to search for
     * @param boolean $caseInSensitive When true a case insensitive compare is performed
     * @return boolean
     */
    public static function startsWith($haystack, $needle, $caseInSensitive = false)
    {
        $len = strlen($needle);
        if ($len == 0) {
            return true;
        }

        if ((strlen($haystack) < $len)) {
            return false;
        }

        if ($caseInSensitive) {
            return strtolower(substr($haystack, 0, $len)) === strtolower($needle);
        }

        return substr($haystack, 0, $len) === (string) $needle;
    }

    /**
     * Return the part after $input and $filter have stopped being the same
     *
     * stripStringLeft('abcdef', 'abcx') => 'def'
     *
     * stripStringLeft('abcdef', 'def') => 'abcdef'
     *
     * @param string $input The text to return part of
     * @param string $filter The text to filter on
     * @return string
     */
    public static function stripStringLeft($input, $filter)
    {
        $count = min(array(strlen($input), strlen($filter)));

        for ($i = 0; $i < $count; $i++) {
            if ($input[$i] != $filter[$i]) {
                break;
            }
        }

        return substr($input, $i);
    }

    /**
     * Strip input url until only host server name is left
     *
     * @param string $input
     * @return string
     */
    public static function stripToHost($input)
    {
        if ($input) {
            $p1 = strpos($input, '://');
            if (false === $p1) {
                $p1 = 0;
                $p2 = strpos($input, '/');
            } else {
                $p1 += 3;
                $p2 = strpos($input, '/', $p1);
            }
            if (false === $p2) {
                $p3 = strlen($input);
            } else {
                $p3 = $p2 - $p1;
            }

            return substr($input, $p1 , $p3);
        }
    }

    /**
     * Cleanup characters not allowed in a cache id.
     *
     * @param string $cacheId
     * @return string
     */
    public static function toCacheId($cacheId)
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $cacheId);
    }
}
