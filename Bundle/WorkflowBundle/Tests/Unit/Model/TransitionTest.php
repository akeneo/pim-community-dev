<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Oro\Bundle\WorkflowBundle\Model\Transition;

class TransitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider propertiesDataProvider
     * @param string $property
     * @param mixed $value
     */
    public function testGettersAndSetters($property, $value)
    {
        $getter = 'get' . ucfirst($property);
        $setter = 'set' . ucfirst($property);
        $obj = new Transition();
        $this->assertInstanceOf(
            'Oro\Bundle\WorkflowBundle\Model\Transition',
            call_user_func_array(array($obj, $setter), array($value))
        );
        $this->assertEquals($value, call_user_func_array(array($obj, $getter), array()));
    }

    public function propertiesDataProvider()
    {
        return array(
            'name' => array('name', 'test'),
            'stepTo' => array('stepTo', $this->getStepMock('testStep')),
            'condition' => array('condition', $this->getMock('Oro\Bundle\WorkflowBundle\Model\ConditionInterface')),
            'postAction' => array('postAction', $this->getMock('Oro\Bundle\WorkflowBundle\Model\PostActionInterface')),
        );
    }

    /**
     * @dataProvider isAllowedDataProvider
     * @param bool $isAllowed
     */
    public function testIsAllowed($isAllowed)
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $condition = $this->getMock('Oro\Bundle\WorkflowBundle\Model\ConditionInterface');
        $condition->expects($this->once())
            ->method('isAllowed')
            ->with($workflowItem)
            ->will($this->returnValue($isAllowed));

        $obj = new Transition();
        $obj->setCondition($condition);
        $this->assertEquals($isAllowed, $obj->isAllowed($workflowItem));
    }

    public function isAllowedDataProvider()
    {
        return array(
            'yes' => array(true),
            'no' => array(false)
        );
    }

    public function testTransitNotAllowed()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();
        $workflowItem->expects($this->never())
            ->method('setCurrentStepName');

        $condition = $this->getMock('Oro\Bundle\WorkflowBundle\Model\ConditionInterface');
        $condition->expects($this->once())
            ->method('isAllowed')
            ->with($workflowItem)
            ->will($this->returnValue(false));

        $postAction = $this->getMock('Oro\Bundle\WorkflowBundle\Model\PostActionInterface');
        $postAction->expects($this->never())
            ->method('execute');

        $obj = new Transition();
        $obj->setCondition($condition);
        $obj->setPostAction($postAction);
        $obj->transit($workflowItem);
    }

    /**
     * @dataProvider transitDataProvider
     * @param boolean $isFinal
     * @param boolean $hasAllowedTransition
     */
    public function testTransit($isFinal, $hasAllowedTransition)
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();
        $workflowItem->expects($this->once())
            ->method('setCurrentStepName')
            ->with('currentStep');
        if ($isFinal || !$hasAllowedTransition) {
            $workflowItem->expects($this->once())
                ->method('setClosed')
                ->with(true);
        } else {
            $workflowItem->expects($this->never())
                ->method('setClosed');
        }

        $condition = $this->getMock('Oro\Bundle\WorkflowBundle\Model\ConditionInterface');
        $condition->expects($this->once())
            ->method('isAllowed')
            ->with($workflowItem)
            ->will($this->returnValue(true));

        $postAction = $this->getMock('Oro\Bundle\WorkflowBundle\Model\PostActionInterface');
        $postAction->expects($this->once())
            ->method('execute')
            ->with($workflowItem);

        $step = $this->getStepMock('currentStep', $isFinal, $hasAllowedTransition);

        $obj = new Transition();
        $obj->setCondition($condition);
        $obj->setPostAction($postAction);
        $obj->setStepTo($step);
        $obj->transit($workflowItem);
    }

    /**
     * @return array
     */
    public function transitDataProvider()
    {
        return array(
            array(true, true),
            array(true, false),
            array(false, false)
        );
    }

    protected function getStepMock($name, $isFinal = false, $hasAllowedTransitions = true)
    {
        $step = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Step')
            ->disableOriginalConstructor()
            ->getMock();
        $step->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));
        $step->expects($this->any())
            ->method('isFinal')
            ->will($this->returnValue($isFinal));
        $step->expects($this->any())
            ->method('hasAllowedTransitions')
            ->will($this->returnValue($hasAllowedTransitions));
        return $step;
    }
}
