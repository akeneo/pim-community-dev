<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action;

use Oro\Bundle\WorkflowBundle\Model\Action\AssignValue;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface;
use Oro\Bundle\WorkflowBundle\Model\Action\UnsetValue;

class UnsetValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AssignValue
     */
    protected $assignValue;

    /**
     * @var ActionInterface
     */
    protected $action;

    protected function setUp()
    {
        $this->assignValue = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Action\AssignValue')
            ->disableOriginalConstructor()
            ->getMock();

        $this->action = new UnsetValue($this->assignValue);
    }

    public function testExecute()
    {
        $context = array();
        $this->assignValue->expects($this->once())
            ->method('execute')
            ->with($context);
        $this->action->execute($context);
    }

    /**
     * @dataProvider optionsDataProvider
     * @param array $options
     * @param array $expected
     */
    public function testInitialize(array $options, array $expected)
    {
        $this->assignValue->expects($this->once())
            ->method('initialize')
            ->with($expected);

        $this->action->initialize($options);
    }

    public function optionsDataProvider()
    {
        return array(
            array(
                array(), array('value' => null)
            ),
            array(
                array('attribute' => 'test'), array('attribute' => 'test', 'value' => null)
            ),
            array(
                array('test'), array('test', null)
            )
        );
    }

    public function testSetCondition()
    {
        $condition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->assignValue->expects($this->once())
            ->method('setCondition')
            ->with($condition);

        $this->action->setCondition($condition);
    }
}
