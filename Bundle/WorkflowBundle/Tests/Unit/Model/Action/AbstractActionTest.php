<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action;

use Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action\Stub\ArrayCondition;

class AbstractActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractAction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $action;

    protected function setUp()
    {
        $this->action = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction')
            ->setConstructorArgs(array(new ContextAccessor()))
            ->getMockForAbstractClass();
    }

    protected function tearDown()
    {
        unset($this->action);
    }

    public function testSetCondition()
    {
        $condition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->action->setCondition($condition);
        $this->assertAttributeEquals($condition, 'condition', $this->action);
    }

    /**
     * @param boolean $expectedAllowed
     * @param boolean|null $conditionAllowed
     * @dataProvider executeDataProvider
     */
    public function testExecute($expectedAllowed, $conditionAllowed = null)
    {
        $context = array('key' => 'value');

        if ($expectedAllowed) {
            $this->action->expects($this->once())
                ->method('executeAction')
                ->with($context);
        } else {
            $this->action->expects($this->never())
                ->method('executeAction');
        }

        if ($conditionAllowed !== null) {
            $condition = new ArrayCondition(array('allowed' => $conditionAllowed));
            $this->action->setCondition($condition);
        }

        $this->action->execute($context);
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return array(
            'no condition' => array(
                'expectedAllowed' => true
            ),
            'allowed condition' => array(
                'expectedAllowed'  => true,
                'conditionAllowed' => true
            ),
            'denied condition' => array(
                'expectedAllowed'  => false,
                'conditionAllowed' => false
            ),
        );
    }
}
