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
class CsvFileIteratorTest extends \PHPUnit_Framework_TestCase
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
     * @return \MUtil_Model_Iterator_CsvFileIterator
     */
    protected function getIterator($filename)
    {
        $iterator = new \MUtil_Model_Iterator_CsvFileIterator($filename);

        return $iterator;
    }
    
    public function testCount()
    {
        $filename = str_replace('.php', '.csv', __FILE__);
        $iterator = $this->getIterator($filename);
        
        $count = $iterator->count();
        $this->assertEquals(3, $count);
    }

    public function testReadAllLines()
    {
        $filename = str_replace('.php', '.csv', __FILE__);
        $iterator = $this->getIterator($filename);
        foreach ($iterator as $line) {
            $actual[] = $line;
        }

        $expected = [
            [
                'line'  => 1,
                'to'    => 'a,',
                'split' => 'b'
            ],
            [
                'line'  => 2,
                'to'    => 'c"',
                'split' => 'd'
            ],
            [
                'line'  => 3,
                'to'    => 'e"',
                'split' => 'f'
            ]
        ];

        $this->assertEquals($expected, $actual);
    }
    
    public function testSerialize()
    {
        $filename = __CLASS__ . '.csv';
        $iterator = $this->getIterator($filename);
        $iterator->next();  //We are at the second record now
        $expected = $iterator->current();
        
        $frozen = serialize($iterator);
        $newIterator = unserialize($frozen);
        
        $actual = $newIterator->current();
        $this->assertEquals($expected, $actual);
    }

}
