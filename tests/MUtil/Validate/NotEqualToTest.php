<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NotEqualToTest
 *
 * @author 175780
 */
class NotEqualToTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid($value, $context)
    {
        $fields = ['a', 'b'];
        $validator = new \MUtil_Validate_NotEqualTo($fields);
        $result = $validator->isValid($value, $context);
        $this->assertTrue($result);
    }
    
    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid($value, $context)
    {
        $fields = ['a', 'b'];
        $validator = new \MUtil_Validate_NotEqualTo($fields);
        $result = $validator->isValid($value, $context);
        $this->assertFalse($result);
    }
    
    /**
     * @dataProvider IsInValidProvider
     */
    public function testgetMessages($value, $context, $expected)
    {
        $fields = ['a', 'b'];
        $messages = ['a' => 'A not OK', 'not OK'];
        $validator = new \MUtil_Validate_NotEqualTo($fields, $messages);
        $validator->isValid($value, $context);
        $result = $validator->getMessages();
        $this->assertEquals($expected, $result['notEqualTo']);
    }

    public function IsValidProvider()
    {
        return [
            'valid#1' => [
                10,
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 10
                ]
            ],
            'valid#2' => [
                10,
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 10
                ]
            ]            
        ];
    }
    
    public function IsInValidProvider()
    {
        return [
            'invalid#1' => [
                1,
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 10
                ],
                'A not OK'
            ],
            'invalid#2' => [
                2,
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 10
                ],
                'not OK'
            ]
            
        ];
    }

}
