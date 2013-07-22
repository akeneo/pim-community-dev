<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\ItemStub;
use Symfony\Component\PropertyAccess\PropertyPath;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

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

    protected function createObject(array $data)
    {
        return new ItemStub($data);
    }
}
