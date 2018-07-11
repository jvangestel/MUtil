<?php

/**
 *
 * @package    MUtil
 * @subpackage Math
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * Decimal calculation utilities
 *
 * RF:  winbill at hotmail dot com 16-Dec-2010 09:31
 * There are issues around float rounding/flooring,
 * use of intermediate typecasting to string (strval) avoids problems
 *
 * jolyon at mways dot co dot uk 10-Aug-2004 11:41
 * The thing to remember here is that the way a float stores a value makes it
 * very easy for these kind of things to happen. When 79.99 is multiplied
 * by 100, the actual value stored in the float is probably something like
 * 7998.9999999999999999999999999999999999, PHP would print out 7999 when the
 * value is displayed but floor would therefore round this down to 7998.
 *
 *
 * @package    MUtil
 * @subpackage Math
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class MUtil_Dec
{
    /**
     * Get the ceiling using the specified precision
     *
     * @param float $number
     * @param int $precision
     * @return float
     */
    public static function ceil($number, $precision)
    {
        $coefficient = pow(10,$precision);
        return ceil(strval($number*$coefficient))/$coefficient;
    }

    /**
     * Get the floor using the specified precision
     *
     * @param float $number
     * @param int $precision
     * @return float
     */
    public static function floor($number, $precision)
    {
        $coefficient = pow(10,$precision);
        return floor(strval($number*$coefficient))/$coefficient;
    }
}
