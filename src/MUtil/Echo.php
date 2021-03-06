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
 * @subpackage Echo
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @version    $Id$
 */

/**
 * This class allows you to echo debug statements over multiple requests by
 * storing the output in the session.
 *
 * @package    MUtil
 * @subpackage Echo
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class MUtil_Echo
{
    /**
     * Add's a backtrace of the position of the code
     * where this function is called.
     *
     * The function itself is not in the backtrace.
     *
     * @return void
     */
    public static function backtrace($start = 0)
    {
        $trace = debug_backtrace(false);

        $content = "\n<h8><b>Print backtrace</b></h8>\n<br/>\n";
        foreach ($trace as $key => $line) {
			if ($start > $key) {
				continue;
			}
            if ($start === $key) {
                // First line is different
                $content .= '<i>Starting backtrace at</i>: ';
            } else {
                if (isset($line['type'])) {
                    $content .= $line['class'] . $line['type'];
                }
                if (isset($line['function'])) {
                    $content .= $line['function'] . '() ';
                }
            }
            if (isset($line['file'])) {
                $content .= $line['file'];
            }
            if (isset($line['line'])) {
                $content .= ' (' . $line['line'] . ')';
            }
            $content .= "<br/>\n";
        }

        $session = self::getSession();
        $session->content .= $content . "\n";
    }

    /**
     * Adds the string output in a format to detect special chars.
     *
     * @param mixed $var_1 Any kind of variable
     * @param mixed $var_2 Optional, any kind of variable
     */
    public static function chars($var_1, $var_2 = null)
    {
        foreach (func_get_args() as $value) {
            $output = '';
            $length = strlen($value);
            for($i = 0; $i < $length; $i++) {
                $output .= '\\' . ord($value[$i]);
            }
            array_map('ord', $value);
            self::r($output, $value);
        }
    }

    /**
     * Adds multiple variables to the output, but displays the class name instead of the class itself.
     *
     * @param mixed $var_1 Any kind of variable
     * @param mixed $var_2 Optional, any kind of variable
     */
    public static function classToName($var_1, $var_2 = null)
    {
        foreach (func_get_args() as $var) {
            self::r(self::convertClassToName($var));
        }
    }

    /**
     * Shows the class name instead of the class content (and)with __toString output if available
     *
     * @param mixed $object
     * @return array
     */
    public static function convertClassToName($object)
    {
        if (is_object($object)) {
            if (method_exists($object, '__toString')) {
                return 'Object class: <strong>' . get_class($object) . '</strong> to string: <em>' .
                        $object->__toString() . '</em>';
            }
            return 'Object class: <strong>' . get_class($object) . '</strong>';
        }
        if (! is_array($object)) {
            return $object;
        }
        $results = array();
        foreach ($object as $key => $value) {
            $results[$key] = self::convertClassToName($value);
        }
        return $results;
    }

    /**
     * Put the function with a name where you want to start counting
     *
     * @param string $name Id of
     */
    public static function countOccurences($name)
    {
        $session = self::getSession();

        if (! isset($session->counts, $session->counts[$name])) {
            $session->counts[$name] = 1;
        } else {
            $session->counts[$name]++;
        }
    }

    /**
     * Returns any information to output.
     *
     * @return mixed
     */
    public static function getOutput()
    {
        $session = self::getSession();
        return $session->content;
    }

    /**
     * Returns the current session namespace that stores the content.
     *
     * @staticvar \Zend_Session_Namespace $session
     * @return \Zend_Session_Namespace
     */
    private static function getSession()
    {
        static $session;

        if (! $session) {
            $session = new \Zend_Session_Namespace('mutil.' . __CLASS__ . '.session');
        }

        return $session;
    }

    /**
     * Returns true if there is information to output.
     *
     * @return boolean
     */
    public static function hasOutput()
    {
        $session = self::getSession();
        return isset($session->content);
    }

    /**
     * Output text as caption
     *
     * @param string $caption
     */
    public static function header($caption)
    {
        $session = self::getSession();

        if ($caption) {
            $session->content .= "\n<h6><b>" . $caption . "</b></h6>\n\n";
        }
    }

    /**
     * Returns and resets the content of the output/
     *
     * @return string Raw html content.
     */
    public static function out()
    {
        $session = self::getSession();
        $content = '';
        if (isset($session->counts)) {
            $content .= "<h6>Count results</h6>\n\n";
            ksort($session->counts);
            foreach ($session->counts as $name => $count) {
                $content .= "<b>$name</b> triggered $count times<br/>\n";
            }
        }
        if (isset($session->timings)) {
            $content .= "<h6>Timer results</h6>\n\n";
            ksort($session->timings);
            foreach ($session->timings as $name => $data) {
                if ($data['level']) {
                    $content .= '<b>Unbalanced call for ' . $name . ':</b> function level remains at ' . $data['level'] .  "!<br/>\n";
                }
                $content .= '<b>' . $name . '</b> total time ' . $data['sum'] . ' in ' . $data['count'] .  " times<br/>\n";
                foreach ($data['times'] as $level => $times) {
                    $content .= '&nbsp;&nbsp;&nbsp;&nbsp;at call level ' . $level . ' total time ' . $times['sum'] . ' in ' . $times['count'] .  " times<br/>\n";
                }
            }
        }
        if (isset($session->content)) {
            $content .= $session->content;
        }
        $session->unsetAll();
        if ($content) {
            $content = "\n<div class='zend_echo'>\n" . $content . "\n</div>\n";
        }

        return $content;
    }

    /**
     * Outputs a wordwrapped string with an optional caption.
     *
     * @param string $var The variable to wordwrap
     * @param string $caption Optional text descibing the variable or moment of debugging.
     */
    public static function pre($var, $caption = null)
    {
        self::r(wordwrap((string) $var, 120), $caption);
    }

    /**
     * Adds the content of a variable and an optional caption to the output.
     *
     * @param mixed $var Any kind of variable
     * @param string $caption Optional text descibing the variable or moment of debugging.
     */
    public static function r($var, $caption = null)
    {
        $session = self::getSession();

        if (is_array($var) || is_object($var) || ($var instanceof __PHP_Incomplete_Class)) {
            ob_start();
            var_dump($var);
            $content = ob_get_clean();
        } elseif (null === $var) {
            $content = "<i>null</i>\n";
        } elseif ('' === $var) {
            $content = "<i>empty string</i>\n";
        } elseif (true === $var) {
            $content = "<i>true</i>\n";
        } elseif (false === $var) {
            $content = "<i>false</i>\n";
        } else {
            $content = $var;
        }
        $content = '<pre>' . $content . "</pre>\n";
        if ($caption) {
            $content = "\n<h6><b>" . $caption . "</b></h6>\n\n" . $content;
        }

        $session->content .= $content;
    }

    /**
     * Adds multiple variables to the output (without captions).
     *
     * @param mixed $var_1 Any kind of variable
     * @param mixed $var_2 Optional, any kind of variable
     */
    public static function rs($var_1, $var_2 = null)
    {
        foreach (func_get_args() as $var) {
            self::r($var);
        }
    }

    /**
     * Put the function with a name where you want to start timing
     *
     * @param string $name Id of
     */
    public static function timeFunctionStart($name)
    {
        $session = self::getSession();

        if (! isset($session->timings, $session->timings[$name])) {
            $session->timings[$name] = array(
                'count' => 0,
                'level' => 0,
                'sum'   => 0,
                'times' => array(),
            );
        }
        $level = $session->timings[$name]['level'];

        if (! isset($session->timings[$name]['times'][$level])) {
            $session->timings[$name]['times'][$level] = array('start' => 0, 'count' => 0, 'sum' => 0);
        }
        $session->timings[$name]['times'][$level]['start'] = microtime(true);
        $session->timings[$name]['level']++;
    }

    /**
     * Put the function with a name where you want to stop timing,
     * must match calls timeFunctionStart() exactly
     *
     * @param string $name Id of
     */
    public static function timeFunctionStop($name)
    {
        $session = self::getSession();

        if (isset($session->timings[$name])) {
            $level = $session->timings[$name]['level'] - 1;

            if (isset($session->timings[$name]['times'][$level]['start'])) {
                $time     = microtime(true) - $session->timings[$name]['times'][$level]['start'];

                $session->timings[$name]['times'][$level]['start'] = null;
                $session->timings[$name]['times'][$level]['count']++;
                $session->timings[$name]['times'][$level]['sum'] += $time;

                $session->timings[$name]['count']++;
                $session->timings[$name]['level']--;
                $session->timings[$name]['sum'] += $time;
            } else {
                self::r("To many stop timing calls for $name at $level.");
            }
        } else {
            self::r("Unknown stop timing call for $name.");
        }
    }

    /**
     * Adds multiple variables to the output with as caption information on the line
     * of code that called this function.
     *
     * @param mixed $var_1 Any kind of variable
     * @param mixed $var_2 Optional, any kind of variable
     */
    public static function track($var_1, $var_2 = null)
    {
        $trace = debug_backtrace(false);

        // Remove this line
        // array_shift($trace);

        $header = 'Track: ';
        if (isset($trace[1]['type'])) {
            $header .= $trace[1]['class'] . $trace[1]['type'];
        }
        if (isset($trace[1]['function'])) {
            $header .= $trace[1]['function'] . '() ';
        }
        if (isset($trace[0]['line'])) {
            $header .= ': ' . $trace[0]['line'];
        }


        foreach (func_get_args() as $var) {
            self::r($var, $header);
            $header = null;
        }
    }
}
