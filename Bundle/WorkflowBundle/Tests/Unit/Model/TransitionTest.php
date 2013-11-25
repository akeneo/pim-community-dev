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
        $ucProp = ucfirst($property);
        $setter = 'set' . $ucProp;
        $obj = new Transition();
        $getter = 'get' . $ucProp;
        if (!method_exists($obj, $getter)) {
            $getter = 'is' . $ucProp;
        }
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
            'label' => array('label', 'test'),
            'message' => array('message', 'test'),
            'hidden' => array('hidden', true),
            'start' => array('start', true),
            'unavailableHidden' => array('unavailableHidden', true),
            'stepTo' => array('stepTo', $this->getStepMock('testStep')),
            'frontendOptions' => array('frontendOptions', array('key' => 'value')),
            'form_type' => array('formType', 'custom_workflow_transition'),
            'form_options' => array('formOptions', array('one', 'two')),
            'pre_condition' => array(
                'preCondition',
                $this->getMock('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface')
            ),
            'condition' => array(
                'condition',
                $this->getMock('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface')
            ),
            'postAction' => array(
                'postAction',
                $this->getMock('Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface')
            ),
            'initAction' => array(
                'initAction',
                $this->getMock('Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface')
            )
        );
    }

    public function testHidden()
    {
        $transition = new Transition();
        $this->assertFalse($transition->isHidden());
        $this->assertInstanceOf(
            'Oro\Bundle\WorkflowBundle\Model\Transition',
            $transition->setHidden(true)
        );
        $this->assertTrue($transition->isHidden());
        $this->assertInstanceOf(
            'Oro\Bundle\WorkflowBundle\Model\Transition',
            $transition->setHidden(false)
        );
        $this->assertFalse($transition->isHidden());
    }

    public function testInitialize()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Transition();
        $action = $this->getMock('Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface');
        $action->expects($this->once())
            ->method('execute')
            ->with($workflowItem);
        $obj->setInitAction($action);
        $obj->initialize($workflowItem);
    }

    /**
     * @dataProvider isAllowedDataProvider
     * @param bool $isAllowed
     * @param bool $expected
     */
    public function testIsAllowed($isAllowed, $expected)
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Transition();

        if (null !== $isAllowed) {
            $condition = $this->getMock('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface');
            $condition->expects($this->once())
                ->method('isAllowed')
                ->with($workflowItem)
                ->will($this->returnValue($isAllowed));
            $obj->setCondition($condition);
        }

        $this->assertEquals($expected, $obj->isAllowed($workflowItem));
    }

    public function isAllowedDataProvider()
    {
        return array(
            'allowed' => array(
                'isAllowed' => true,
                'expected'  => true
            ),
            'not allowed' => array(
                'isAllowed' => false,
                'expected'  => false,
            ),
            'no condition' => array(
                'isAllowed' => null,
                'expected'  => true,
            ),
        );
    }

    /**
     * @dataProvider isAllowedDataProvider
     * @param bool $isAllowed
     * @param bool $expected
     */
    public function testIsAvailableWithForm($isAllowed, $expected)
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Transition();
        $obj->setFormOptions(array('key' => 'value'));

        if (null !== $isAllowed) {
            $condition = $this->getMock('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface');
            $condition->expects($this->once())
                ->method('isAllowed')
                ->with($workflowItem)
                ->will($this->returnValue($isAllowed));
            $obj->setPreCondition($condition);
        }

        $this->assertEquals($expected, $obj->isAvailable($workflowItem));
    }

    /**
     * @dataProvider isAvailableDataProvider
     * @param bool $isAllowed
     * @param bool $isAvailable
     * @param bool $expected
     */
    public function testIsAvailableWithoutForm($isAllowed, $isAvailable, $expected)
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Transition();

        if (null !== $isAvailable) {
            $preCondition = $this->getMock('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface');
            $preCondition->expects($this->any())
                ->method('isAllowed')
                ->with($workflowItem)
                ->will($this->returnValue($isAvailable));
            $obj->setPreCondition($preCondition);
        }
        if (null !== $isAllowed) {
            $condition = $this->getMock('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface');
            $condition->expects($this->any())
                ->method('isAllowed')
                ->with($workflowItem)
                ->will($this->returnValue($isAllowed));
            $obj->setCondition($condition);
        }

        $this->assertEquals($expected, $obj->isAvailable($workflowItem));
    }

    public function isAvailableDataProvider()
    {
        return array(
            'allowed' => array(
                'isAllowed' => true,
                'isAvailable' => true,
                'expected'  => true
            ),
            'not allowed #1' => array(
                'isAllowed' => false,
                'isAvailable' => true,
                'expected'  => false,
            ),
            'not allowed #2' => array(
                'isAllowed' => true,
                'isAvailable' => false,
                'expected'  => false,
            ),
            'not allowed #3' => array(
                'isAllowed' => false,
                'isAvailable' => false,
                'expected'  => false,
            ),
            'no conditions' => array(
                'isAllowed' => null,
                'isAvailable' => null,
                'expected'  => true,
            ),
        );
    }

    /**
     * @dataProvider transitDisallowedDataProvider
     * @param bool $preConditionAllowed
     * @param bool $conditionAllowed
     */
    public function testTransitNotAllowed($preConditionAllowed, $conditionAllowed)
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();
        $workflowItem->expects($this->never())
            ->method('setCurrentStepName');

        $condition = $this->getMock('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface');
        $condition->expects($this->any())
            ->method('isAllowed')
            ->with($workflowItem)
            ->will($this->returnValue($conditionAllowed));

        $preCondition = $this->getMock('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface');
        $preCondition->expects($this->any())
            ->method('isAllowed')
            ->with($workflowItem)
            ->will($this->returnValue($preConditionAllowed));

        $postAction = $this->getMock('Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface');
        $postAction->expects($this->never())
            ->method('execute');

        $obj = new Transition();
        $obj->setPreCondition($preCondition);
        $obj->setCondition($condition);
        $obj->setPostAction($postAction);
        $obj->transit($workflowItem);
    }

    public function transitDisallowedDataProvider()
    {
        return array(
            array(false, false),
            array(false, true),
            array(true, false)
        );
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

        $condition = $this->getMock('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface');
        $condition->expects($this->once())
            ->method('isAllowed')
            ->with($workflowItem)
            ->will($this->returnValue(true));

        $preCondition = $this->getMock('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface');
        $preCondition->expects($this->once())
            ->method('isAllowed')
            ->with($workflowItem)
            ->will($this->returnValue(true));

        $postAction = $this->getMock('Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface');
        $postAction->expects($this->once())
            ->method('execute')
            ->with($workflowItem);

        $step = $this->getStepMock('currentStep', $isFinal, $hasAllowedTransition);

        $obj = new Transition();
        $obj->setCondition($condition);
        $obj->setPreCondition($preCondition);
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

    public function testStart()
    {
        $obj = new Transition();
        $this->assertFalse($obj->isStart());
        $obj->setStart(true);
        $this->assertTrue($obj->isStart());
    }

    public function testGetSetFrontendOption()
    {
        $obj = new Transition();

        $this->assertEquals(array(), $obj->getFrontendOptions());

        $frontendOptions = array('class' => 'foo', 'icon' => 'bar');
        $obj->setFrontendOptions($frontendOptions);
        $this->assertEquals($frontendOptions, $obj->getFrontendOptions());
    }

    public function testHasForm()
    {
        $obj = new Transition();

        $this->assertFalse($obj->hasForm()); // by default transition has form

        $obj->setFormOptions(array('key' => 'value'));
        $this->assertTrue($obj->hasForm());
    }
}
