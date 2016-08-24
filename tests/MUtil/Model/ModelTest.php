<?php

class ModelTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->model = $this->getMockForAbstractClass('MUtil_Model_ModelAbstract', array('testAbstractModel'));
    }
       
    /**
     * @dataProvider providerAddFilterDataProvider
     */
    public function testAddFilter($initial, $extra, $expected)
    {
        $model = $this->model;
        $model->setFilter($initial);
        $model->addFilter($extra);
        $this->assertEquals($expected, $model->getFilter());
    }
    
    /**
     * 
     * @return array
     */
    public function providerAddFilterDataProvider()
    {
        return array(
            array(  // Simple first test
                array(
                    'testfield' => 'stringvalue'
                    ),
                array(
                    'field2'    => 2
                    ),
                array(
                    'testfield' => 'stringvalue',
                    'field2'    => 2
                    )
            ),
            array(  // Check remove duplicates
                array(
                    'testfield' => 'stringvalue'
                    ),
                array(
                    'stringvalue'
                    ),
                array(
                    'testfield' => 'stringvalue'
                    )
            ),
            array(  // Check remove duplicates
                array(
                    'testfield' => 'stringvalue'
                    ),
                array(
                    'testfield' => 'stringvalue'
                    ),
                array(
                    'testfield' => 'stringvalue'
                    )
            ),
            array(  // Check mixed types
                array(
                    'testfield' => 1
                    ),
                array(
                    'stringvalue'
                    ),
                array(
                    'testfield' => 1,
                    0           => 'stringvalue'
                    )
            ),
            array(  // Check mixed types bug #838
                array(
                    'testfield' => 0
                    ),
                array(
                    'stringvalue',
                    'test'
                    ),
                array(
                    'testfield' => 0,
                    0           => 'stringvalue',
                    1           => 'test'
                    )
            )
        );
    }
    
    
}
