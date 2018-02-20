<?php

/**
 *
 * @package    MUtil
 * @subpackage File
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2018, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 */

/**
 *
 * @package    MUtil
 * @subpackage File
 * @copyright  Copyright (c) 2018, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.4 20-Feb-2018 12:06:58
 */
class MUtil_FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test isOnWindows function
     */
    public function testIsOnWindows()
    {
        // Use a different method to establish being on windows
        $this->assertEquals(strncasecmp(PHP_OS, 'WIN', 3) == 0, \MUtil_File::isOnWindows());
    }

    /**
     * Test data provider for testRemoveWindowsDriveLetter
     *
     * @return array
     */
    public function providerRemoveWindowsDriveLetter()
    {
        return [
            ['d:\path', '\path'],
            ['d:path', 'path'],
            ['\path', '\path'],
            ['path', 'path'],
            ['p\ath', 'p\ath'],
            ['7:\path', '7:\path'],
            ['http:\\path', 'http:\\path'],
        ];
    }

    /**
     * @param string $input
     * @param string $output
     *
     * @dataProvider providerRemoveWindowsDriveLetter
     */
    public function testRemoveWindowsDriveLetter($input, $output)
    {
        $this->assertEquals($output, \MUtil_File::removeWindowsDriveLetter($input));
    }
}
