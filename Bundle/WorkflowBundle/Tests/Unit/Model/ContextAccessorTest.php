<?php
namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

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

    protected function createObject(array $data)
    {
        $result = new \stdClass();
        foreach ($data as $property => $value) {
            $result->$property = $value;
        }
        return $result;
    }
}
