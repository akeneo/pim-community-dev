<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Model\PostAction\AbstractPostAction;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction\Stub\ArrayCondition;

class AbstractPostActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractPostAction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $postAction;

    protected function setUp()
    {
        $this->postAction = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\AbstractPostAction')
            ->setConstructorArgs(array(new ContextAccessor()))
            ->getMockForAbstractClass();
    }

    protected function tearDown()
    {
        unset($this->postAction);
    }

    public function testSetCondition()
    {
        $condition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->postAction->setCondition($condition);
        $this->assertAttributeEquals($condition, 'condition', $this->postAction);
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
            $this->postAction->expects($this->once())
                ->method('executeAction')
                ->with($context);
        } else {
            $this->postAction->expects($this->never())
                ->method('executeAction');
        }

        if ($conditionAllowed !== null) {
            $condition = new ArrayCondition(array('allowed' => $conditionAllowed));
            $this->postAction->setCondition($condition);
        }

        $this->postAction->execute($context);
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
