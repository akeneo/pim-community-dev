<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\ItemStub;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Zend\Server\Reflection;

class ContextAccessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextAccessor
     */
    protected $contextAccessor;

    protected function setUp()
    {
        $this->contextAccessor = new ContextAccessor();
    }

    /**
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($context, $value, $expectedValue)
    {
        $this->assertEquals($expectedValue, $this->contextAccessor->getValue($context, $value));
    }

    public function getValueDataProvider()
    {
        return array(
            'get_simple_value' => array(
                'context' => array('foo' => 'bar'),
                'value' => 'test',
                'expectedValue' => 'test'
            ),
            'get_property_from_array' => array(
                'context' => array('foo' => 'bar'),
                'value' => new PropertyPath('[foo]'),
                'expectedValue' => 'bar'
            ),
            'get_property_from_object' => array(
                'context' => $this->createObject(array('foo' => 'bar')),
                'value' => new PropertyPath('foo'),
                'expectedValue' => 'bar'
            ),
            'get_nested_property_from_object' => array(
                'context' => $this->createObject(array('foo' => $this->createObject(array('bar' => 'baz')))),
                'value' => new PropertyPath('foo.bar'),
                'expectedValue' => 'baz'
            ),
            'get_unknown_property' => array(
                'context' => $this->createObject(array('foo' => 'bar')),
                'value' => new PropertyPath('baz'),
                'expectedValue' => null
            ),
        );
    }

    /**
     * @dataProvider setValueDataProvider
     * @depends      testGetValue
     */
    public function testSetValue($context, $property, $value, $expectedValue)
    {
        $this->contextAccessor->setValue($context, $property, $value);
        $actualValue = $this->contextAccessor->getValue($context, $property);
        $this->assertEquals($expectedValue, $actualValue);
    }

    public function setValueDataProvider()
    {
        return array(
            'set_simple_new_property' => array(
                'context' => $this->createObject(array()),
                'property' => new PropertyPath('test'),
                'value' => 'value',
                'expectedValue' => 'value'
            ),
            'set_simple_existing_property_text_path' => array(
                'context' => $this->createObject(array('foo' => 'bar')),
                'property' => new PropertyPath('foo'),
                'value' => 'test',
                'expectedValue' => 'test'
            ),
            'set_existing_property_to_new' => array(
                'context' => $this->createObject(array('foo' => 'bar')),
                'property' => new PropertyPath('test'),
                'value' => new PropertyPath('foo'),
                'expectedValue' => 'bar'
            ),
            'set_existing_property_to_existing' => array(
                'context' => $this->createObject(array('foo' => 'bar', 'test' => 'old')),
                'property' => new PropertyPath('test'),
                'value' => new PropertyPath('foo'),
                'expectedValue' => 'bar'
            ),
            'nested_property_from_object_to_new' => array(
                'context' => $this->createObject(array('foo' => $this->createObject(array('bar' => 'baz')))),
                'property' => new PropertyPath('test'),
                'value' => new PropertyPath('foo.bar'),
                'expectedValue' => 'baz'
            ),
            'nested_property_from_object_to_existing' => array(
                'context' => $this->createObject(
                    array('test' => 'old', 'foo' => $this->createObject(array('bar' => 'baz')))
                ),
                'property' => new PropertyPath('test'),
                'value' => new PropertyPath('foo.bar'),
                'expectedValue' => 'baz'
            ),
            'unknown_property' => array(
                'context' => $this->createObject(array('foo' => 'bar')),
                'property' => new PropertyPath('test'),
                'value' => new PropertyPath('baz'),
                'expectedValue' => null
            ),
        );
    }

    /**
     * @dataProvider hasValueDataProvider
     */
    public function testHasValue($context, $value, $expectedValue)
    {
        $this->contextAccessor->hasValue($context, $value);
        $actualValue = $this->contextAccessor->hasValue($context, $value);
        $this->assertEquals($expectedValue, $actualValue);
    }

    public function hasValueDataProvider()
    {
        return array(
            'not_has' => array(
                'context' => $this->createObject(array()),
                'value' => new PropertyPath('test'),
                'expectedValue' => false
            ),
            'not_has_nested' => array(
                'context' => $this->createObject(array('foo' => $this->createObject(array('bar' => 'baz')))),
                'value' => new PropertyPath('data[foo].baz'),
                'expectedValue' => false
            ),
            'has_as_array_syntax' => array(
                'context' => $this->createObject(array('foo' => 'bar')),
                'value' => new PropertyPath('data[foo]'),
                'expectedValue' => true
            ),
            'has_as_object_syntax' => array(
                'context' => $this->createObject(array('foo' => 'bar')),
                'value' => new PropertyPath('data[foo]'),
                'expectedValue' => true
            ),
            'has_nested' => array(
                'context' => $this->createObject(array('foo' => $this->createObject(array('bar' => 'baz')))),
                'value' => new PropertyPath('data[foo].data'),
                'expectedValue' => true
            ),
            'has_nested_nested' => array(
                'context' => $this->createObject(array('foo' => $this->createObject(array('bar' => 'baz')))),
                'value' => new PropertyPath('data[foo].data.bar'),
                'expectedValue' => true
            ),
        );
    }

    public function testGetValueNoSuchProperty()
    {
        $context = $this->createObject(array());
        $value = new PropertyPath('test');

        $propertyAccessor = $this->getMockBuilder('Symfony\Component\PropertyAccess\PropertyAccessor')
            ->disableOriginalConstructor()
            ->setMethods(array('getValue'))
            ->getMock();
        $propertyAccessor->expects($this->once())
            ->method('getValue')
            ->with($context, $value)
            ->will($this->throwException(new NoSuchPropertyException('No such property')));

        $propertyAccessorReflection = new \ReflectionProperty(
            'Oro\Bundle\WorkflowBundle\Model\ContextAccessor',
            'propertyAccessor'
        );
        $propertyAccessorReflection->setAccessible(true);
        $propertyAccessorReflection->setValue($this->contextAccessor, $propertyAccessor);

        $this->assertNull($this->contextAccessor->getValue($context, $value));
    }

    protected function createObject(array $data)
    {
        return new ItemStub($data);
    }
}
