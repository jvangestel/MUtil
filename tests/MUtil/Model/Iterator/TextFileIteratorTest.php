<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MUtil\Model\Iterator;

/**
 * Description of TextFileIteratorTest
 *
 * @author 175780
 */
class TextFileIteratorTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * 
     * @param type $filename
     * @param type $split
     * @param type $encoding
     * @return \MUtil_Model_Iterator_TextFileIterator
     */
    protected function getIterator($filename, $split = ',', $encoding = '')
    {
        $splitObject = new \MUtil_Model_Iterator_TextLineSplitter($split, $encoding);
        if ($encoding) {
            $splitFunc = array($splitObject, 'splitRecoded');
        } else {
            $splitFunc = array($splitObject, 'split');
        }

        $iterator = new \MUtil_Model_Iterator_TextFileIterator($filename, $splitFunc);

        return $iterator;
    }

    public function testReadAllLines()
    {
        $filename = str_replace('.php', '.txt', $this->getTemplateFileName());
        $iterator = $this->getIterator($filename);
        foreach ($iterator as $line) {
            $actual[] = $line;
        }

        $expected = [
            [
                'line'  => 1,
                'to'    => 'a',
                'split' => 'b'
            ],
            [
                'line'  => 2,
                'to'    => 'c',
                'split' => 'd'
            ],
            [
                'line'  => 3,
                'to'    => 'e',
                'split' => 'f'
            ]
        ];

        $this->assertEquals($expected, $actual);
    }
    
    public function testSerialize()
    {
        $filename = __CLASS__ . '.txt';
        $iterator = $this->getIterator($filename);
        $iterator->next();  //We are at the second record now
        $expected = $iterator->current();
        
        $frozen = serialize($iterator);
        $newIterator = unserialize($frozen);
        
        $actual = $newIterator->current();
        $this->assertEquals($expected, $actual);
    }

}
