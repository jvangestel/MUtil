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
 * @subpackage Parser_Sql
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @version    $Id$
 */

include_once 'MUtil/Parser/Sql/WordsParserException.php';

/**
 * Parses a statement into SQL 'word', where quoted strings, fields or database
 * object names and comments are seen as a single word each, even when
 * containing whitespace.
 *
 * @package    MUtil
 * @subpackage Parser_Sql
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class MUtil_Parser_Sql_WordsParser
{
    const MODE_WORD = 0;
    const MODE_WHITESPACE = 1;
    const MODE_BRACKET = 2;
    const MODE_COMMA = 3;
    const MODE_SEMI_COLON = 4;
    const MODE_QUOTED_STRING = 100;
    const MODE_DOUBLE_QUOTED_STRING = 101;
    const MODE_LINE_COMMENT = 102;
    const MODE_ACCESS_NAME = 104;
    const MODE_MULTI_LINE_COMMENT = 201;

    private $_makeWordFunction;
    private $_len;
    private $_lenMinusOne;
    private $_line;
    private $_pos;
    private $_start;
    private $_statement;

    /**
     *
     *  $makeWordFunction should be a function accepting these
     *  parameters and returning an object that should be added to
     *  the array returned as a result from splitStatement():
     *
     *      makeWord($word, $is_word, $start_line, $start_char)
     *
     *  The default implementations does returns an array of words,
     *  throwing out the positional and word information.
     *
     * @param string $statements The sql statements to parse
     * @param callable $makeWordFunction A function with the parameters:
     *                      function($word, $is_word, $start_line, $start_char)
     */
    public function __construct($statements, $makeWordFunction = null)
    {
        $this->_statement   = $statements;
        $this->_len         = strlen($statements);
        $this->_lenMinusOne = $this->_len - 1;
        $this->_line        = 1;
        $this->_pos         = 1;
        $this->_start       = 0;
        if ($makeWordFunction) {
            $this->_makeWordFunction = $makeWordFunction;
        } else {
            $this->_makeWordFunction = array(__CLASS__, 'makeWord');
        }
    }

    private function findCharEnd($pos, $char, $startLine = 0, $startPos = 0)
    {
        if ($startLine == 0) {
            $startLine = $this->_line;
            $startPos = $this->_pos;
        }

        while ((++$pos < $this->_len) && ($this->_statement[$pos] != $char)) {
            $this->setLine($pos);

            // Check for escape
            if ($this->_statement[$pos] == '\\') {
                $pos++;
                $this->_pos++;
            }
        }

        if ($pos >= $this->_len) {
            throw new \MUtil_Parser_Sql_WordsParserException('Opening '.$char.' does not close', $startLine, $startPos);
        }

        // Check for character repeat
        if (($pos < $this->_lenMinusOne) && ($this->_statement[$pos + 1] == $char)) {
            $this->_pos += 2;
            return $this->findCharEnd($pos + 1, $char, $startLine, $startPos);
        }

        return $pos;
    }

    private function findCharsEnd($pos, $chars, $startLine = 0, $startPos = 0)
    {
        $char1 = $chars[0];
        $char2 = $chars[1];

        if ($startLine == 0) {
            $startLine = $this->_line;
            $startPos = $this->_pos;
        }

        while ((++$pos < $this->_lenMinusOne) && (! (($this->_statement[$pos] == $char1) && ($this->_statement[$pos + 1] == $char2)))) {
            $this->setLine($pos);
        }

        if ($pos >= $this->_len) {
            throw new \MUtil_Parser_Sql_WordsParserException('Opening '.$chars.' does not close', $startLine, $startPos);
        }

        $this->setLine(++$pos);

        return $pos;
    }

    private function findLineEnd($pos)
    {
        $endPos = strpos($this->_statement, "\n", $pos + 1);

        if ($endPos === false) {
            return $this->_len;
        }

        // One less on Windows line end
        if ($this->_statement[$endPos - 1] == "\r") {
            return $endPos - 2;
        }
        return $endPos - 1;
    }

    /**
     * Default implementation for returning tokens from splitStatement.
     * 
     * Note that all this implementation do is return the $word. If
     * you want to use the rest than make your own function that
     * keeps the information at hand.
     *
     * @param $word       The new word.
     * @param $isWord    This is a word, not a comment or whitespace.
     * @param $startLine The (start) line of the current word.
     * @param $startChar The (start) character of the current word.
     */
    public static function makeWord($word, $isWord, $startLine, $startChar)
    {
        return $word;
    }

    private function mode($pos)
    {
        switch ($this->_statement[$pos]) {
            case ' ':
            case "\n":
            case "\t":
            case "\r":
                return self::MODE_WHITESPACE;
            case '\'':
                return self::MODE_QUOTED_STRING;
            case '"':
                return self::MODE_DOUBLE_QUOTED_STRING;
            case ',':
                return self::MODE_COMMA;
            case ';':
                return self::MODE_SEMI_COLON;
            case '(':
            case ')':
                return self::MODE_BRACKET;
            case '#':
                return self::MODE_LINE_COMMENT;
            case '[':
                return self::MODE_ACCESS_NAME;
            case '-':
                if (($pos < $this->_lenMinusOne) && ($this->_statement[$pos + 1] == '-')) {
                    return self::MODE_LINE_COMMENT;
                }
            case '/':
                if (($pos < $this->_lenMinusOne) && ($this->_statement[$pos + 1] == '*') && ($this->_statement[$pos] != '-')) {
                    return self::MODE_MULTI_LINE_COMMENT;
                }

            default:
                // Last ditch check
                if (ctype_space($this->_statement[$pos])) {
                    return self::MODE_WHITESPACE;
                }

                return self::MODE_WORD;
        }
    }

    private static function modeIsOneChar($mode)
    {
        switch ($mode) {
            case self::MODE_COMMA;
            case self::MODE_SEMI_COLON;
            case self::MODE_BRACKET;
                return true;
        }

        return false;
    }

    private static function modeIsWord($mode)
    {
        switch ($mode) {
            case self::MODE_WHITESPACE:
            case self::MODE_LINE_COMMENT:
                return false;
        }

        return true;
    }

    private static function modeNotComment($mode)
    {
        switch ($mode) {
            case self::MODE_LINE_COMMENT:
            case self::MODE_MULTI_LINE_COMMENT:
                return false;
        }

        return true;
    }

    private function setLine($pos)
    {
        if ($this->_statement[$pos] == "\n") {
            $this->_line++;
            $this->_pos = 0;
        } else {
            $this->_pos++;
        }
    }

    /**
     * Split the next statement into word parts
     *
     * @param boolean $keepComments When false comment statements are removed from the output
     * @return array Of sql 'words'
     */
    public function splitStatement($keepComments = true)
    {
        $pos = $this->_start;

        if ($pos >= $this->_len) {
            return false;
        }

        switch ($nextMode = $this->mode($pos)) {
            case self::MODE_QUOTED_STRING:
                $pos = $this->findCharEnd($pos, '\'');
                break;
            case self::MODE_DOUBLE_QUOTED_STRING:
                $pos = $this->findCharEnd($pos, '"');
                break;
            case self::MODE_LINE_COMMENT:
                $pos = $this->findLineEnd($pos) + 1;
                break;
            case self::MODE_ACCESS_NAME:
                $pos = $this->findCharEnd($pos, ']');
                break;
            case self::MODE_MULTI_LINE_COMMENT:
                 $pos = $this->findCharsEnd($pos, '*/') + 1;
                break;
        }
        $modeStart = $pos;
        $modeStartLine = $this->_line;
        $modeStartChar = $this->_pos;

        $sql = array();

        while ($pos < $this->_len) {

            // Take care of positioning
            $this->setLine($pos);

            $thisMode = $nextMode;
            $nextMode = $this->mode($pos);

            if (($thisMode != $nextMode) || self::modeIsOneChar($thisMode)) {
                if ($keepComments || self::modeNotComment($thisMode)) {
                    $sql[] = call_user_func($this->_makeWordFunction, substr($this->_statement, $modeStart, $pos - $modeStart), self::modeIsWord($thisMode), $modeStartLine, $modeStartChar);
                }
                $modeStart = $pos;
                $modeStartLine = $this->_line;
                $modeStartChar = $this->_pos;

                switch ($nextMode) {
                    case self::MODE_QUOTED_STRING:
                        $pos = $this->findCharEnd($pos, '\'');
                        break;
                    case self::MODE_DOUBLE_QUOTED_STRING:
                        $pos = $this->findCharEnd($pos, '"');
                        break;
                    case self::MODE_LINE_COMMENT:
                        $pos = $this->findLineEnd($pos);
                        break;
                    case self::MODE_ACCESS_NAME:
                        $pos = $this->findCharEnd($pos, ']');
                        break;
                    case self::MODE_MULTI_LINE_COMMENT:
                        $pos = $this->findCharsEnd($pos, '*/');
                        break;
                    case self::MODE_SEMI_COLON:
                        $this->_start = $pos + 1;
                        return $sql;
                }
            }

            $pos++;
        }
        // BUG WARNING: Use $next_mode in this line because the while loop has just
        // exited, so the current mode is the next mode.
        if ($sql && ($keepComments || self::modeNotComment($nextMode))) {
            $sql[] = call_user_func($this->_makeWordFunction, substr($this->_statement, $modeStart, $pos - $modeStart), self::modeIsWord($thisMode), $modeStartLine, $modeStartChar);
        }
        $this->_start = $this->_len;

        return $sql;
    }

    /**
     * Split the whole input into statements
     *
     * @param string $statements One or more SQL statements separated by ';' semicolumns
     * @param boolean $keepComments When false comment statements are removed from the output
     * @param boolean $makeStrings Return the individual statements as (trimmed) strings instead of arrays
     * @param callable $makeWordFunction A function with the parameters:
     *                      function($word, $is_word, $start_line, $start_char)
     * @return array Of statements
     */
    public static function splitStatements($statements, $keepComments = true, $makeStrings = true, $makeWordFunction = null)
    {
        $parser = new self($statements, $makeWordFunction);

        $stmts = array();
    
        do {
            $stmt = $parser->splitStatement($keepComments);
            if ($stmt === false) {
                // We reached end of string
                break;
            }
            if ($makeStrings) {
                $sql = implode('', $stmt);

                if (strlen(trim($sql))) {
                    $stmts[] = trim($sql);
                }
            } else {
                $stmts[] = $stmt;
            }
        } while (1);
        
        return $stmts;
    }
}
