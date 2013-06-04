<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\EntityAutocomplete\Transformer;

use Oro\Bundle\FormBundle\EntityAutocomplete\Property;
use Oro\Bundle\FormBundle\EntityAutocomplete\Configuration;
use Oro\Bundle\FormBundle\EntityAutocomplete\Transformer\EntityToTextTransformer;

class EntityToTextTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Configuration
     */
    protected $configuration;

    /**
     * @var EntityToTextTransformer
     */
    protected $transformer;

    protected function setUp()
    {
        $this->configuration = $this->getMockBuilder('Oro\Bundle\FormBundle\EntityAutocomplete\Configuration')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transformer = new EntityToTextTransformer($this->configuration);
    }

    /**
     * @dataProvider transformDataProvider
     * @param array|object $value
     * @param array $properties
     * @param string $expected
     */
    public function testTransform($value, $properties, $expected)
    {
        $this->configuration->expects($this->once())
            ->method('getAutocompleteOptions')
            ->with('test')
            ->will($this->returnValue($properties));

        $this->assertEquals($expected, $this->transformer->transform('test', $value));
    }

    /**
     * @return array
     */
    public function transformDataProvider()
    {
        return array(
            'no value, no properties 1' => array(
                null, null, ''
            ),
            'no value, no properties 2' => array(
                null, array(), ''
            ),
            'no value, no properties 3' => array(
                null, array('properties' => null), ''
            ),
            'no value, properties' => array(
                null, array('properties' => array($this->getPropertyMock('name'))), ''
            ),
            'value array, no properties 1' => array(
                array('name' => 'test'), null, ''
            ),
            'value array, no properties 2' => array(
                array('name' => 'test'), array(), ''
            ),
            'value array, no properties 3' => array(
                array('name' => 'test'), array('properties' => null), ''
            ),
            'value object, no properties 1' => array(
                $this->getValueObjectMock(array('getName' => 'test')), null, ''
            ),
            'value object, no properties 2' => array(
                $this->getValueObjectMock(array('getName' => 'test')), array(), ''
            ),
            'value object, no properties 3' => array(
                $this->getValueObjectMock(array('getName' => 'test')), array('properties' => null), ''
            ),

            'value array, properties unknown' => array(
                array('name' => 'test'), array('properties' => array($this->getPropertyMock('unknown'))), ''
            ),
            'value array, one property' => array(
                array('name' => 'test'), array('properties' => array($this->getPropertyMock('name'))), 'test'
            ),
            'value array, more than one property' => array(
                array('name' => 'test', 'second_name' => 'second'),
                array('properties' => array($this->getPropertyMock('name'), $this->getPropertyMock('second_name'))),
                'test second'
            ),

            'value object, properties unknown' => array(
                $this->getValueObjectMock(array('getName' => 'test')), array('properties' => array($this->getPropertyMock('unknown'))), ''
            ),
            'value object, one property' => array(
                $this->getValueObjectMock(array('getName' => 'test')), array('properties' => array($this->getPropertyMock('name'))), 'test'
            ),
            'value object, more than one property' => array(
                $this->getValueObjectMock(array('getName' => 'test', 'getSecondName' => 'second')),
                array('properties' => array($this->getPropertyMock('name'), $this->getPropertyMock('second_name'))),
                'test second'
            ),
        );
    }

    protected function getValueObjectMock(array $data)
    {
        $mock = $this->getMockBuilder('\stdClass')
            ->setMethods(array_keys($data))
            ->getMock();
        foreach ($data as $method => $val) {
            $mock->expects($this->once())
                ->method($method)
                ->will($this->returnValue($val));
        }
        return $mock;
    }

    protected function getPropertyMock($name)
    {
        $mock = $this->getMockBuilder('Oro\Bundle\FormBundle\EntityAutocomplete\Property')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));
        return $mock;
    }
}
