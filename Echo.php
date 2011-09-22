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
 */

/**
 * @package MUtil
 */

class MUtil_Echo
{
    public static function backtrace()
    {
    $trace = debug_backtrace(false);

    $content = "\n<h8><b>Print backtrace</b></h8>\n<br/>\n";
    foreach ($trace as $key => $line) {
        if (0 === $key) {
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

    private static function getSession()
    {
        static $session;

        if (! $session) {
            $session = new Zend_Session_Namespace('mutil.' . __CLASS__ . '.session');
        }

        return $session;
    }

    public static function out()
    {
        $session = self::getSession();
        if (isset($session->content)) {
            $content = $session->content;
            $session->unsetAll();

            if ($content) {
                $content = "\n<div class='zend_echo'>\n" . $content . "\n</div>\n";
            }

            return $content;
        }
    }

    public static function pre($var, $caption = null)
    {
        self::r(wordwrap((string) $var, 120), $caption);
    }

    public static function r($var, $caption = null)
    {
        $session = self::getSession();

        if (is_array($var) || is_object($var)) {
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

    public static function rs($var_1, $var_2 = null)
    {
        foreach (func_get_args() as $var) {
            self::r($var);
        }
    }
    
    public static function track($var_1, $var_2 = null)
    {
        $trace = debug_backtrace(false);

        // Remove this line
        // array_shift($trace);

        $header = 'Track: ';
        if (isset($trace[1]['type'])) {
            $header .= $trace[1]['class'] . $trace[1]['type'];
        }
        $header .= $trace[1]['function'] . '() ';
        if (isset($trace[0]['line'])) {
            $header .= ': ' . $trace[0]['line'];
        }

        
        foreach (func_get_args() as $var) {
            self::r($var, $header);
            $header = null;
        }
    }
}