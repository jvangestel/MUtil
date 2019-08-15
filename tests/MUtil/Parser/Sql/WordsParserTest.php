<?php

/**
 *
 * @package    MUtil
 * @subpackage Parser_Sql
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 *
 *
 * @package    MUtil
 * @subpackage Parser_Sql
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class WordsParserTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @var MUtil_Parser_Sql_WordsParser
     */
    protected $_parser;

    /**
     * Test script containing all kind of comments, quoted values, etc...
     *
     * @var string
     */
    protected $_sql = "
SELECT /* comment */ Something as [Really Something] FROM Nothing;

-- Hi Mom

UPDATE Nothing SET Something = '\" /* -- bla' WHERE SomethingElse = \"'quoted'\";

-- Bye mom
";

    /**
     * Result array output without comments, contains the full whitespace
     *
     * This shows you how the raw split is performed
     *
     * @var array
     */
    protected $_sqlOutputArray = array(
        array("
", "SELECT", " ", // Here was a comment
            " ", "Something", " ", "as", " ", "[Really Something]", " ", "FROM", " ", "Nothing"),
        array("

", // another comment
"

", "UPDATE", " ", "Nothing", " ", "SET", " ", "Something", " ", "=", " ", "'\" /* -- bla'", " ",
            "WHERE", " ", "SomethingElse", " ", "=", " ", "\"'quoted'\""),
        array("

", // Third comment
"
"));

    /**
     * Result string output without comments
     *
     * @var array
     */
    protected $_sqlOutputString = array(
        "SELECT  Something as [Really Something] FROM Nothing",
        "UPDATE Nothing SET Something = '\" /* -- bla' WHERE SomethingElse = \"'quoted'\"");

    public function setUp()
    {
        $this->_parser = new MUtil_Parser_Sql_WordsParser($this->_sql);
    }

    public function testParse()
    {
        $result = $this->_parser->splitStatement(false);
        $this->assertEquals($result[1], 'SELECT');
        $this->assertCount(13, $result);
    }

    public function testParseComment()
    {
        $result = $this->_parser->splitStatement(true);
        $this->assertEquals($result[3], '/* comment */');
        $this->assertCount(14, $result);
    }

    public function testSplitAllArray()
    {
        $result = MUtil_Parser_Sql_WordsParser::splitStatements($this->_sql, false, false);
        $this->assertCount(3, $result);
        // $this->assertEquals($result[0], $this->_sqlOutputArray[0]);
        // $this->assertEquals($result[1], $this->_sqlOutputArray[1]);
        // $this->assertEquals($result[2], $this->_sqlOutputArray[2]);
        $this->assertEquals($result, $this->_sqlOutputArray);
    }

    public function testSplitAllString()
    {
        $result = MUtil_Parser_Sql_WordsParser::splitStatements($this->_sql, false, true);
        $this->assertCount(2, $result);
        $this->assertEquals($result, $this->_sqlOutputString);
    }
    
    /**
     * @dataProvider splitEndingsProvider
     */
    public function testSplitEndings($statements)
    {
        $result = MUtil_Parser_Sql_WordsParser::splitStatements($statements, false);
        $this->assertCount(2, $result);
    }
    
    public function splitEndingsProvider()
    {
        return [
            'Unix' => [
                "INSERT INTO `sometable` (`id`, `Age`) VALUES\n(1, 1);\nINSERT INTO `sometable` (`id`, `Age`) VALUES\n(1, 1);\n"
            ],
            'Windows' => [
                "INSERT INTO `sometable` (`id`, `Age`) VALUES\r\n(1, 1);\nINSERT INTO `sometable` (`id`, `Age`) VALUES\r\n(1, 1);\r\n"
            ],
            'Mac' => [
                "INSERT INTO `sometable` (`id`, `Age`) VALUES\r(1, 1);\nINSERT INTO `sometable` (`id`, `Age`) VALUES\n(1, 1);\r"
            ],
            'UnixComment' => [
                "/*!40101 */;INSERT INTO `sometable` (`id`, `Age`) VALUES\n(1, 1);\nINSERT INTO `sometable` (`id`, `Age`) VALUES\n(1, 1);\n"
            ],
            'WindowsComment' => [
                "/*!40101 */;INSERT INTO `sometable` (`id`, `Age`) VALUES\r\n(1, 1);\nINSERT INTO `sometable` (`id`, `Age`) VALUES\r\n(1, 1);\r\n"
            ],
            'MacComment' => [
                "/*!40101 */;INSERT INTO `sometable` (`id`, `Age`) VALUES\r(1, 1);\nINSERT INTO `sometable` (`id`, `Age`) VALUES\n(1, 1);\r"
            ]
        ];
    }
}
