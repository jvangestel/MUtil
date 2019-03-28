<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NoTags
 *
 * @author 175780
 */
class NoTagsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid($value)
    {
        $validator = new \MUtil_Validate_NoTags();
        $result = $validator->isValid($value);
        $this->assertTrue($result);
    }
    
    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid($value)
    {
        $validator = new \MUtil_Validate_NoTags();
        $result = $validator->isValid($value);
        $this->assertFalse($result);
    }

    public function IsValidProvider()
    {
        return [
            'valid#1' => [ "< allowed" ],
            'valid#2' => [ "<1" ],
            'valid#2' => [ "tom&jerry@wb.com" ]
        ];
    }
    
    public function IsInValidProvider()
    {
        return [
            'invalid#1' => [ "<abc" ],
            'invalid#2' => [ "<ABC" ],
            'invalid#3' => [ "</abc" ],
            'invalid#4' => [ "<\abc" ],
            'invalid#5' => [ "<:abc" ],
            'invalid#6' => [ "&nbsp;" ],
            'invalid#7' => [ "&#160;" ],
            'invalid#8' => [ "&#x000A0;" ],
            'invalid#9' => [ "&#X000A0;" ]
        ];
    }

}
