<?php

/**
 *
 * @package    MUtil
 * @subpackage String
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */


/**
 * Unit test for class \MUtil_String
 *
 * @package    MUtil
 * @subpackage String
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5.7
 */
class MUtil_StringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Return empty string if charFilters hit on first character
     */
    public function testBeforeCharsReturnNone()
    {
        $result = \MUtil_String::beforeChars('abcdef', 'rara');
        $this->assertEquals($result, '');
    }

    /**
     * Return part of the input when charFilters hits later
     */
    public function testBeforeCharsReturnPart()
    {
        $result = \MUtil_String::beforeChars('abcdef', 'god');
        $this->assertEquals($result, 'abc');
    }

    /**
     * Return the whole input if no charFilters characters where there
     */
    public function testBeforeCharsReturnAll()
    {
        $result = \MUtil_String::beforeChars('abcdef', 'xyz');
        $this->assertEquals($result, 'abcdef');
    }

    /**
     * Return true when the needle is contained in the haystack
     */
    public function testContainsIndeed()
    {
        $result = \MUtil_String::contains('abcdefg', 'def');
        $this->assertEquals($result, true);
    }

    /**
     * Return true when the needle is contained in the haystack, starting at the first character
     */
    public function testContainsIndeedStart()
    {
        $result = \MUtil_String::contains('abcdef', 'abc');
        $this->assertEquals($result, true);
    }

    /**
     * Return false when the needle is not contained in the haystack
     */
    public function testContainsNot()
    {
        $result = \MUtil_String::contains('abcdef', 'xyz');
        $this->assertEquals($result, false);
    }

    /**
     * Haystack ends with needle but wrong case
     */
    public function testEndsWithCaseFalse()
    {
        $result = \MUtil_String::endsWith('abcdef', 'deF');
        $this->assertEquals($result, false);
    }

    /**
     * Haystack does not end with needle
     */
    public function testEndsWithFalse()
    {
        $result = \MUtil_String::endsWith('abcdef', 'xdef');
        $this->assertEquals($result, false);
    }

    /**
     * Needle is empty
     */
    public function testEndsWithNeedleEmpty()
    {
        $result = \MUtil_String::endsWith('abcdef', '');
        $this->assertEquals($result, true);
    }

    /**
     * Needle is longer than haystack
     */
    public function testEndsWithNeedleLonger()
    {
        $result = \MUtil_String::endsWith('abc', 'abcdef');
        $this->assertEquals($result, false);
    }

    /**
     * Haystack ends with needle that is number (should work)
     */
    public function testEndsWithNumber()
    {
        $result = \MUtil_String::endsWith('abc10', 10);
        $this->assertEquals($result, true);
    }

    /**
     * Haystack ends with needle but only case insensitively
     */
    public function testEndsWithNoCaseTrue()
    {
        $result = \MUtil_String::endsWith('abCdef', 'Def', true);
        $this->assertEquals($result, true);
    }

    /**
     * Haystack ends with needle
     */
    public function testEndsWithTrue()
    {
        $result = \MUtil_String::endsWith('abcdef', 'def');
        $this->assertEquals($result, true);
    }

    /**
     * An invalid base 64 string
     */
    public function testIsBase64False()
    {
        $result = \MUtil_String::isBase64('YW55IGNhcm5hbCBwbGVhc3=y');
        $this->assertEquals($result, false);
    }

    /**
     * An invalid base 64 string that is not a multiple of 4 characters
     */
    public function testIsBase64FalseLength()
    {
        $result = \MUtil_String::isBase64('a=b&c=d');
        $this->assertEquals($result, false);
    }

    /**
     * A valid base 64 string ending with '='
     */
    public function testIsBase64Is1()
    {
        $result = \MUtil_String::isBase64('YW55IGN+cm5hbCBwbGVhc3U=');
        $this->assertEquals($result, true);
    }

    /**
     * A valid base 64 string ending with '=='
     */
    public function testIsBase64Is2()
    {
        $result = \MUtil_String::isBase64('YW55IGNh/m5hbCBwbGVhcw==');
        $this->assertEquals($result, true);
    }

    /**
     * An invalid base 64 string ending with '==='
     */
    public function testIsBase64Is3()
    {
        $result = \MUtil_String::isBase64('YW55IGNhcm5hbCBwbGVhc===');
        $this->assertEquals($result, false);
    }

    /**
     * A valid base 64 string
     */
    public function testIsBase64NoIs()
    {
        $result = \MUtil_String::isBase64('YW55IGNhcm5hbCBwbGVhc3Vy');
        $this->assertEquals($result, true);
    }

    /**
     * A valid base 64 string
     */
    public function testIsBase64Plus()
    {
        $result = \MUtil_String::isBase64('YW55I+Nhcm5+bCBwbGVhc3Vy');
        $this->assertEquals($result, true);
    }

    /**
     * A valid base 64 string
     */
    public function testIsBase64Slash()
    {
        $result = \MUtil_String::isBase64('YW55I/Nhcm5/bCBwbGVhc3Vy');
        $this->assertEquals($result, true);
    }

    /**
     * A valid base 64 string
     */
    public function testIsBase64SlashPlus()
    {
        $result = \MUtil_String::isBase64('YW55I+Nhcm5+bC/w/GVhc3Vy');
        $this->assertEquals($result, true);
    }


    /**
     * Test a default callback usage
     */
    public function testSplitOnCharCallbackDefault()
    {
        $result = \MUtil_String::splitOnCharCallback('abcDef', 'ctype_upper');
        $test[0] = 'abc';
        $test[1] = 'Def';
        $this->assertEquals($result, $test);
    }

    /**
     * Test callback usage, with two consecutive delimiters
     */
    public function testSplitOnCharCallbackDoubleD()
    {
        $result = \MUtil_String::splitOnCharCallback('abcDDef', 'ctype_upper');
        $test[0] = 'abc';
        $test[1] = 'D';
        $test[2] = 'Def';
        $this->assertEquals($result, $test);
    }

    /**
     * Test a callback usage where nothing happens
     */
    public function testSplitOnCharCallbackEmptyString()
    {
        $result = \MUtil_String::splitOnCharCallback('', 'ctype_upper');
        $this->assertEquals($result, array());
    }

    /**
     * Test callback usage, excluding the delimiter
     */
    public function testSplitOnCharCallbackNoDelimeter()
    {
        $result = \MUtil_String::splitOnCharCallback('abcDef', 'ctype_upper', true);
        $test[0] = 'abc';
        $test[1] = 'ef';
        $this->assertEquals($result, $test);
    }

    /**
     * Test callback usage where there are no delimiters
     */
    public function testSplitOnCharCallbackNosplit()
    {
        $result = \MUtil_String::splitOnCharCallback('abcdef', 'ctype_upper');
        $test[0] = 'abcdef';
        $this->assertEquals($result, $test);
    }

    /**
     * Test callback usage, with two consecutive caps, excluding the delimiter
     */
    public function testSplitOnCharCallbackNoDelimiterDoubleD()
    {
        $result = \MUtil_String::splitOnCharCallback('abcDDef', 'ctype_upper', true);
        $test[0] = 'abc';
        $test[2] = 'ef';
        $this->assertEquals($result, $test);
    }

    /**
     * Test callback usage with another function
     */
    public function testSplitOnCharCallbackNumeric()
    {
        $result = \MUtil_String::splitOnCharCallback('ab1cD2ef', 'is_numeric');
        $test[0] = 'ab';
        $test[1] = '1cD';
        $test[2] = '2ef';
        $this->assertEquals($result, $test);
    }

    /**
     * Haystack starts with needle but wrong case
     */
    public function testStartsWithCaseFalse()
    {
        $result = \MUtil_String::startsWith('abcdef', 'abC');
        $this->assertEquals($result, false);
    }

    /**
     * Haystack does not start with needle
     */
    public function testStartsWithFalse()
    {
        $result = \MUtil_String::startsWith('abcdef', 'abcx');
        $this->assertEquals($result, false);
    }

    /**
     * Needle is empty
     */
    public function testStartsWithNeedleEmpty()
    {
        $result = \MUtil_String::startsWith('abcdef', '');
        $this->assertEquals($result, true);
    }

    /**
     * Needle is longer
     */
    public function testStartsWithNeedleLonger()
    {
        $result = \MUtil_String::startsWith('abc', 'abcdef');
        $this->assertEquals($result, false);
    }

    /**
     * Haystack starts with numberic needle that is the same (is allowed)
     */
    public function testStartsWithNumber()
    {
        $result = \MUtil_String::startsWith('10abc', 10);
        $this->assertEquals($result, true);
    }

    /**
     * Haystack starts with needle, but only case-insentivile
     */
    public function testStartsWithNoCaseTrue()
    {
        $result = \MUtil_String::startsWith('abCdef', 'abC', true);
        $this->assertEquals($result, true);
    }

    /**
     * Haystack starts with needle
     */
    public function testStartsWithTrue()
    {
        $result = \MUtil_String::startsWith('abcdef', 'abc');
        $this->assertEquals($result, true);
    }

    /**
     * Remove the characters where both strings are the same
     */
    public function testStripStringLeftRemovepartFilter()
    {
        $result = \MUtil_String::stripStringLeft('abcdef', 'abcx');
        $this->assertEquals($result, 'def');
    }

    /**
     * Remove the characters where the input string starts with the filter
     */
    public function testStripStringLeftRemoveWholeFilter()
    {
        $result = \MUtil_String::stripStringLeft('abcdef', 'abc');
        $this->assertEquals($result, 'def');
    }

    /**
     * Remove nothing as both strings have no common starting characters
     */
    public function testStripStringLeftNothing()
    {
        $result = \MUtil_String::stripStringLeft('abcdef', 'xabc');
        $this->assertEquals($result, 'abcdef');
    }

    public function providerTestStripToHost()
    {
        return array(
            array('https://www.host.com/erwrtej/gfeg', 'www.host.com'),
            array('http://www.host.com/erwrtej/gfeg', 'www.host.com'),
            array('http://www.host.com/', 'www.host.com'),
            array('http://www.host.com', 'www.host.com'),
            array('ftp://www.host.com/', 'www.host.com'),
            array('ftp://www.host.com:32/', 'www.host.com:32'),
            array('://www.host.com/', 'www.host.com'),
            array('www.host.com/dfgjkdf/dffd', 'www.host.com'),
            array('www.host.com/', 'www.host.com'),
            array('www.host.com', 'www.host.com'),
            array('http:///', ''),
            array(null, null),
            );
    }

    /**
     *
     * @param type $input
     * @param type $output
     *
     * @dataProvider providerTestStripToHost
     */
    public function testStripToHostTest($input, $output)
    {
        $result = \MUtil_String::stripToHost($input);
        $this->assertEquals($result, $output);
    }
}
