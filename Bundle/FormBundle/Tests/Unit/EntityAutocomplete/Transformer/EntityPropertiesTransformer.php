<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\EntityAutocomplete\Transformer;

use Oro\Bundle\FormBundle\EntityAutocomplete\Property;
use Oro\Bundle\FormBundle\EntityAutocomplete\Transformer\EntityPropertiesTransformer;

class EntityPropertiesTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityPropertiesTransformer
     */
    protected $transformer;

    /**
     * @dataProvider transformDataProvider
     * @param array|object $value
     * @param Property[] $properties
     * @param array $expected
     */
    public function testTransform($value, array $properties, array $expected)
    {
        $this->transformer = new EntityPropertiesTransformer($properties);
        $this->assertEquals($expected, $this->transformer->transform($value));
    }

    /**
     * @return array
     */
    public function transformDataProvider()
    {
        return array(
            'no value, no properties' => array(
                null, array(), array()
            ),
            'no value, properties' => array(
                null, array($this->getPropertyMock('name')), array()
            ),
            'value array, no properties' => array(
                array('name' => 'test'), array(), array('id' => null)
            ),
            'value array, no properties and id' => array(
                array('id' => 1, 'name' => 'test'), array(), array('id' => 1)
            ),
            'value object, no properties' => array(
                $this->getValueObjectMock(array('getId' => 1, 'getName' => 'test')), array(), array('id' => 1)
            ),
            'value array, properties unknown and id' => array(
                array('id' => 1, 'name' => 'test'),
                array($this->getPropertyMock('unknown')),
                array('id' => 1, 'unknown' => null)
            ),
            'value array, one property' => array(
                array('name' => 'test'),
                array($this->getPropertyMock('name')),
                array('id' => null, 'name' => 'test')
            ),
            'value array, more than one property' => array(
                array('name' => 'test', 'second_name' => 'second'),
                array($this->getPropertyMock('name'), $this->getPropertyMock('second_name')),
                array('id' => null, 'name' => 'test', 'second_name' => 'second')
            ),
            'value object method, properties unknown' => array(
                $this->getValueObjectMock(array('getName' => 'test')),
                array($this->getPropertyMock('unknown')),
                array('id' => null, 'unknown' => null)
            ),
            'value object method, one property' => array(
                $this->getValueObjectMock(array('getName' => 'test')),
                array($this->getPropertyMock('name')),
                array('id' => null, 'name' => 'test')
            ),
            'value object method, more than one property' => array(
                $this->getValueObjectMock(array('getName' => 'test', 'getSecondName' => 'second')),
                array($this->getPropertyMock('name'), $this->getPropertyMock('second_name')),
                array('id' => null, 'name' => 'test', 'second_name' => 'second')
            ),

            'value object property, properties unknown' => array(
                (object)array('name' => 'test'),
                array($this->getPropertyMock('unknown')),
                array('id' => null, 'unknown' => null)
            ),
            'value object property, one property' => array(
                (object)array('name' => 'test'),
                array($this->getPropertyMock('name')),
                array('id' => null, 'name' => 'test')
            ),
            'value object property, more than one property' => array(
                (object)array('name' => 'test', 'second_name' => 'second'),
                array($this->getPropertyMock('name'), $this->getPropertyMock('second_name')),
                array('id' => null, 'name' => 'test', 'second_name' => 'second')
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
        $mock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));
        return $mock;
    }
}
