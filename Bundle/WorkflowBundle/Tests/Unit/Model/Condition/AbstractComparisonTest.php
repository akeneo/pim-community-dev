<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\Condition;
use Oro\Bundle\WorkflowBundle\Model\Condition\AbstractComparison;

class AbstractComparisonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractComparison|\PHPUnit_Framework_MockObject_MockObject
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
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionException
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
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionException
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
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionException
     * @expectedExceptionMessage Options must have 2 elements, but 0 given
     */
    public function testInitializeFailsWithInvalidOptionsCount()
    {
        $this->condition->initialize(array());
    }

    public function testAddError()
    {
        $context = array('foo' => 'fooValue', 'bar' => 'barValue');
        $options = array('left' => 'foo', 'right' => 'bar');

        $left = $options['left'];
        $right = $options['right'];

        $this->condition->initialize($options);
        $message = 'Compare {{ left }} with {{ right }}.';
        $this->condition->setMessage($message);

        $this->contextAccessor->expects($this->at(0))->method('getValue')
            ->with($context, $left)
            ->will($this->returnValue($context[$left]));

        $this->contextAccessor->expects($this->at(1))->method('getValue')
            ->with($context, $right)
            ->will($this->returnValue($context[$right]));

        $this->condition->expects($this->once())->method('doCompare')
            ->with($context[$left], $context[$right])
            ->will($this->returnValue(false));

        $this->contextAccessor->expects($this->at(2))->method('getValue')
            ->with($context, $left)
            ->will($this->returnValue($context[$left]));

        $this->contextAccessor->expects($this->at(3))->method('getValue')
            ->with($context, $right)
            ->will($this->returnValue($context[$right]));

        $errors = new ArrayCollection();

        $this->assertFalse($this->condition->isAllowed($context, $errors));

        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            array(
                'message' => $message,
                'parameters' => array('{{ left }}' => $context[$left], '{{ right }}' => $context[$right])
            ),
            $errors->get(0)
        );
    }
}
