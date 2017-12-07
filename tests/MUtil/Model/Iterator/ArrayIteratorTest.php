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
class ArrayIteratorTest extends \PHPUnit_Framework_TestCase
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
     * @return \MUtil\Model\Iterator\ArrayIteratorTellable
     */
    protected function getIterator(array $input)
    {
        $iterator = new ArrayIteratorTellable($input);

        return $iterator;
    }

    /**
     * If this test fails, the normal ArrayIterator retains it's position
     * after serialization and then we can remove our own extension
     */
    public function testBasicArrayIterator()
    {
        $input = [
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
        $iterator = new \ArrayIterator($input);

        $iterator->next();  //We are at the second record now
        $expected = $iterator->current();

        $frozen = serialize($iterator);
        $newIterator = unserialize($frozen);

        $actual = $newIterator->current();
        try {
            $this->assertEquals($expected, $actual);
            $this->markTestSkipped("Current PHP version " . phpversion() . " handles serializing ArrayIterator correct\n");
        } catch (\PHPUnit_Framework_ExpectationFailedException $exc) {
            $this->markTestSkipped("Current PHP version " . phpversion() . " does not handle serializing ArrayIterator correct\n");
        }        
    }

    public function testReadAllElements()
    {
        $input = [
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
        $iterator = $this->getIterator($input);

        $expected = $input;
        foreach($iterator as $row) {
            $actual[] = $row;
        }

        $this->assertEquals($expected, $actual);
    }

    public function testSerialize()
    {
        $input = [
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
        $iterator = $this->getIterator($input);

        $iterator->next();  //We are at the second record now
        $expected = $iterator->current();

        $frozen = serialize($iterator);
        $newIterator = unserialize($frozen);

        $actual = $newIterator->current();
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * Empty iterator resulted in error when unserializing
     * with error Seek position 0 is out of range
     */
    public function testEmpty()
    {
        $iterator = $this->getIterator(array());
        
        // This should work just fine
        foreach($iterator as $row) {            
        }
        
        $frozen = serialize($iterator);
        // This is where the error occured
        $new = unserialize($frozen);
        
        // Just to prove we can loop
        foreach($new as $row) {            
        }
        
        $this->assertEquals(0, count($new));        
    }

}
