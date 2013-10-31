<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionParametersParser;

class MassActionParametersParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var MassActionParametersParser */
    protected $parser;

    public function setUp()
    {
        $this->request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()->getMock();
        $this->parser = new MassActionParametersParser();
    }

    public function tearDown()
    {
        unset($this->request);
        unset($this->parser);
    }

    /**
     * @dataProvider parseDataProvider
     */
    public function testParse(array $inputData, array $expectedData)
    {
        $increment = 0;
        foreach ($inputData as $param => $value) {
            $this->request->expects($this->at($increment))->method('get')->with($param)
                ->will($this->returnValue($value));

            $increment++;
        }

        $this->assertEquals($expectedData, $this->parser->parse($this->request));
    }

    /**
     * @return array
     */
    public function parseDataProvider()
    {
        return array(
            array(
                array(
                    'inset'   => false,
                    'values'  => array(1, 2),
                    'filters' => null
                ),
                array(
                    'inset'   => false,
                    'values'  => array(1, 2),
                    'filters' => array()
                )
            ),
            array(
                array(
                    'inset'   => true,
                    'values'  => '1,2,3',
                    'filters' => null
                ),
                array(
                    'inset'   => true,
                    'values'  => array(1, 2, 3),
                    'filters' => array()
                )
            ),
            array(
                array(
                    'inset'   => true,
                    'values'  => '1,2,3',
                    'filters' => null
                ),
                array(
                    'inset'   => true,
                    'values'  => array(1, 2, 3),
                    'filters' => array()
                )
            ),
            'bad json filter data' => array(
                array(
                    'inset'   => true,
                    'values'  => '1,2,3',
                    'filters' => 'some bad string'
                ),
                array(
                    'inset'   => true,
                    'values'  => array(1, 2, 3),
                    'filters' => array()
                )
            ),
            'good json filter data' => array(
                array(
                    'inset'   => true,
                    'values'  => '1,2,3',
                    'filters' => '{"someFilter":"someValue"}'
                ),
                array(
                    'inset'   => true,
                    'values'  => array(1, 2, 3),
                    'filters' => array('someFilter' => 'someValue')
                )
            )
        );
    }
}
