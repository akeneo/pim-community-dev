<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class AbstractComparisonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $condition;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextAccessor;

    protected function setUp()
    {
        $this->contextAccessor = $this->getMock(
            'Oro\Bundle\WorkflowBundle\Model\ContextAccessor',
            array('getValue')
        );
        $this->condition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\AbstractComparison')
            ->setConstructorArgs(array($this->contextAccessor))
            ->getMockForAbstractClass();
    }

    /**
     * @dataProvider isAllowedDataProvider
     * @param array $options
     * @param array $context
     * @param boolean $expectedValue
     */
    public function testIsAllowed(array $options, array $context, $expectedValue)
    {
        $this->condition->initialize($options);

        $right = end($options);
        $left = reset($options);

        $this->contextAccessor->expects($this->at(0))->method('getValue')
            ->with($context, $left)
            ->will($this->returnValue($context[$left]));

        $this->contextAccessor->expects($this->at(1))->method('getValue')
            ->with($context, $right)
            ->will($this->returnValue($context[$right]));

        $this->condition->expects($this->once())->method('doCompare')
            ->with($context[$left], $context[$right])
            ->will($this->returnValue($expectedValue));

        $this->assertEquals($expectedValue, $this->condition->isAllowed($context));
    }

    public function isAllowedDataProvider()
    {
        return array(
            array(
                array('left' => 'foo', 'right' => 'bar'),
                array('foo' => 'fooValue', 'bar' => 'barValue'),
                true
            ),
            array(
                array('foo', 'bar'),
                array('foo' => 'fooValue', 'bar' => 'barValue'),
                true
            ),
            array(
                array('left' => 'foo', 'right' => 'bar'),
                array('foo' => 'fooValue', 'bar' => 'barValue'),
                false
            ),
            array(
                array('foo', 'bar'),
                array('foo' => 'fooValue', 'bar' => 'barValue'),
                false
            ),
        );
    }

    public function testInitializePasses()
    {
        $this->condition->initialize(
            array(
                'left' => 'foo',
                'right' => 'bar'
            )
        );
        $this->assertAttributeEquals('foo', 'left', $this->condition);
        $this->assertAttributeEquals('bar', 'right', $this->condition);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionOptionRequiredException
     * @expectedExceptionMessage Option "right" is required.
     */
    public function testInitializeFailsWithEmptyRightOption()
    {
        $this->condition->initialize(
            array(
                'foo' => 'bar',
                'left' => 'foo'
            )
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionOptionRequiredException
     * @expectedExceptionMessage Option "left" is required.
     */
    public function testInitializeFailsWithEmptyLeftOption()
    {
        $this->condition->initialize(
            array(
                'right' => 'foo',
                'foo' => 'bar',
            )
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionInitializationException
     * @expectedExceptionMessage Options must have 2 elements, but 0 given
     */
    public function testInitializeFailsWithInvalidOptionsCount()
    {
        $this->condition->initialize(array());
    }
}
