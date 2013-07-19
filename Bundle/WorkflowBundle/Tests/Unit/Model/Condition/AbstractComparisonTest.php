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

        $this->contextAccessor->expects($this->at(0))->method('getValue')
            ->with($context, $options['left'])
            ->will($this->returnValue($context[$options['left']]));

        $this->contextAccessor->expects($this->at(1))->method('getValue')
            ->with($context, $options['right'])
            ->will($this->returnValue($context[$options['right']]));

        $this->condition->expects($this->once())->method('doCompare')
            ->with($context[$options['left']], $context[$options['right']])
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
                array('left' => 'foo', 'right' => 'bar'),
                array('foo' => 'fooValue', 'bar' => 'barValue'),
                false
            )
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
                'right' => 'foo'
            )
        );
    }
}
