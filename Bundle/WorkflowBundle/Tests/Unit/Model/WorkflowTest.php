<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\Transition;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowTransitionRecord;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class WorkflowTest extends \PHPUnit_Framework_TestCase
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
        $workflow = $this->createWorkflow();
        $this->assertInstanceOf(
            'Oro\Bundle\WorkflowBundle\Model\Workflow',
            call_user_func_array(array($workflow, $setter), array($value))
        );
        $this->assertEquals($value, call_user_func_array(array($workflow, $getter), array()));
    }

    public function propertiesDataProvider()
    {
        return array(
            'name' => array('name', 'test'),
            'label' => array('label', 'test')
        );
    }

    public function testType()
    {
        $workflow = $this->createWorkflow();
        $this->assertNull($workflow->getType());
        $value = Workflow::TYPE_ENTITY;
        $workflow->setType($value);
        $this->assertEquals($value, $workflow->getType());
    }

    public function testEnabled()
    {
        $workflow = $this->createWorkflow();
        $this->assertTrue($workflow->isEnabled());

        $workflow->setEnabled(false);
        $this->assertFalse($workflow->isEnabled());

        $workflow->setEnabled(true);
        $this->assertTrue($workflow->isEnabled());
    }

    public function testGetStepsEmpty()
    {
        $workflow = $this->createWorkflow();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $workflow->getStepManager()->getSteps());
    }

    public function testGetOrderedSteps()
    {
        $stepOne = new Step();
        $stepOne->setOrder(1);
        $stepTwo = new Step();
        $stepTwo->setOrder(2);
        $stepThree = new Step();
        $stepThree->setOrder(3);
        $steps = new ArrayCollection(array($stepTwo, $stepOne, $stepThree));

        $workflow = $this->createWorkflow();
        $workflow->getStepManager()->setSteps($steps);
        $ordered = $workflow->getStepManager()->getOrderedSteps();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $ordered);
        $this->assertSame($stepOne, $ordered->get(0), 'Steps are not in correct order');
        $this->assertSame($stepTwo, $ordered->get(1), 'Steps are not in correct order');
        $this->assertSame($stepThree, $ordered->get(2), 'Steps are not in correct order');
    }

    public function testSetSteps()
    {
        $stepOne = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Step')
            ->getMock();
        $stepOne->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('step1'));

        $stepTwo = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Step')
            ->getMock();
        $stepTwo->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('step2'));

        $workflow = $this->createWorkflow();

        $workflow->getStepManager()->setSteps(array($stepOne, $stepTwo));
        $steps = $workflow->getStepManager()->getSteps();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $steps);
        $expected = array('step1' => $stepOne, 'step2' => $stepTwo);
        $this->assertEquals($expected, $steps->toArray());

        $stepsCollection = new ArrayCollection(array('step1' => $stepOne, 'step2' => $stepTwo));
        $workflow->getStepManager()->setSteps($stepsCollection);
        $steps = $workflow->getStepManager()->getSteps();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $steps);
        $expected = array('step1' => $stepOne, 'step2' => $stepTwo);
        $this->assertEquals($expected, $steps->toArray());
    }

    public function testGetTransitionsEmpty()
    {
        $workflow = $this->createWorkflow();
        $this->assertInstanceOf(
            'Doctrine\Common\Collections\ArrayCollection',
            $workflow->getTransitionManager()->getTransitions()
        );
    }

    public function testGetTransition()
    {
        $transition = $this->getTransitionMock('transition');

        $workflow = $this->createWorkflow();
        $workflow->getTransitionManager()->setTransitions(array($transition));

        $this->assertEquals($transition, $workflow->getTransitionManager()->getTransition('transition'));
    }

    public function testSetTransitions()
    {
        $transitionOne = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Transition')
            ->getMock();
        $transitionOne->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('transition1'));

        $transitionTwo = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Transition')
            ->getMock();
        $transitionTwo->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('transition2'));

        $workflow = $this->createWorkflow();

        $workflow->getTransitionManager()->setTransitions(array($transitionOne, $transitionTwo));
        $transitions = $workflow->getTransitionManager()->getTransitions();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $transitions);
        $expected = array('transition1' => $transitionOne, 'transition2' => $transitionTwo);
        $this->assertEquals($expected, $transitions->toArray());

        $transitionsCollection = new ArrayCollection(
            array('transition1' => $transitionOne, 'transition2' => $transitionTwo)
        );
        $workflow->getTransitionManager()->setTransitions($transitionsCollection);
        $transitions = $workflow->getTransitionManager()->getTransitions();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $transitions);
        $expected = array('transition1' => $transitionOne, 'transition2' => $transitionTwo);
        $this->assertEquals($expected, $transitions->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected transition argument type is string or Transition
     */
    public function testIsTransitionAllowedArgumentException()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $workflow = $this->createWorkflow();
        $workflow->isTransitionAllowed($workflowItem, 1);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected transition argument type is string or Transition
     */
    public function testTransitAllowedArgumentException()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $workflow = $this->createWorkflow();
        $workflow->transit($workflowItem, 1);
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException
     * @expectedExceptionMessage Step "test_step" of workflow "test_workflow" doesn't have allowed transition "test_transition".
     */
    // @codingStandardsIgnoreEnd
    public function testIsTransitionAllowedStepHasNoAllowedTransitionException()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $workflowItem->expects($this->once())
            ->method('getCurrentStepName')
            ->will($this->returnValue('test_step'));

        $workflowItem->expects($this->once())
            ->method('getWorkflowName')
            ->will($this->returnValue('test_workflow'));

        $step = $this->getStepMock('test_step');
        $step->expects($this->any())
            ->method('isAllowedTransition')
            ->with('test_transition')
            ->will($this->returnValue(false));

        $transition = $this->getTransitionMock('test_transition', false);

        $workflow = $this->createWorkflow('test_workflow');
        $workflow->getTransitionManager()->setTransitions(array($transition));
        $workflow->getStepManager()->setSteps(array($step));

        $workflow->isTransitionAllowed($workflowItem, 'test_transition', null, true);
    }

    /**
     * @dataProvider isTransitionAllowedDataProvider
     */
    public function testIsTransitionAllowed(
        $expectedResult,
        $transitionExist,
        $transitionAllowed,
        $isTransitionStart,
        $hasCurrentStep,
        $stepAllowTransition,
        $fireExceptions = true
    ) {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $workflowItem->expects($this->any())
            ->method('getWorkflowName')
            ->will($this->returnValue('test_workflow'));

        $workflowItem->expects($this->any())
            ->method('getCurrentStepName')
            ->will($this->returnValue($hasCurrentStep ? 'test_step' : null));

        $step = $this->getStepMock('test_step');
        $step->expects($this->any())
            ->method('isAllowedTransition')
            ->with('test_transition')
            ->will($this->returnValue($stepAllowTransition));

        $errors = new ArrayCollection();

        $transition = $this->getTransitionMock('test_transition', $isTransitionStart);
        $transition->expects($this->any())
            ->method('isAllowed')
            ->with($workflowItem, $errors)
            ->will($this->returnValue($transitionAllowed));

        $workflow = $this->createWorkflow('test_workflow');
        if ($transitionExist) {
            $workflow->getTransitionManager()->setTransitions(array($transition));
        }
        $workflow->getStepManager()->setSteps(array($step));

        if ($expectedResult instanceof \Exception) {
            $this->setExpectedException(get_class($expectedResult), $expectedResult->getMessage());
        }

        $actualResult = $workflow->isTransitionAllowed($workflowItem, 'test_transition', $errors, $fireExceptions);

        if (is_bool($expectedResult)) {
            $this->assertEquals($actualResult, $expectedResult);
        }
    }

    public function isTransitionAllowedDataProvider()
    {
        return array(
            'not_allowed_transition' => array(
                'expectedResult' => false,
                'transitionExist' => true,
                'transitionAllowed' => false,
                'isTransitionStart' => true,
                'hasCurrentStep' => true,
                'stepAllowTransition' => true,
            ),
            'allowed_transition' => array(
                'expectedResult' => true,
                'transitionExist' => true,
                'transitionAllowed' => true,
                'isTransitionStart' => true,
                'hasCurrentStep' => true,
                'stepAllowTransition' => true,
            ),
            'not_allowed_start_transition' => array(
                'expectedResult' => false,
                'transitionExist' => true,
                'transitionAllowed' => false,
                'isTransitionStart' => false,
                'hasCurrentStep' => true,
                'stepAllowTransition' => true,
            ),
            'allowed_start_transition' => array(
                'expectedResult' => true,
                'transitionExist' => true,
                'transitionAllowed' => true,
                'isTransitionStart' => false,
                'hasCurrentStep' => true,
                'stepAllowTransition' => true,
            ),
            'unknown_transition_fire_exception' => array(
                'expectedException' => InvalidTransitionException::unknownTransition('test_transition'),
                'transitionExist' => false,
                'transitionAllowed' => true,
                'isTransitionStart' => false,
                'hasCurrentStep' => true,
                'stepAllowTransition' => true,
            ),
            'unknown_transition_no_exception' => array(
                'expectedResult' => false,
                'transitionExist' => false,
                'transitionAllowed' => true,
                'isTransitionStart' => false,
                'hasCurrentStep' => true,
                'stepAllowTransition' => true,
                'fireException' => false
            ),
            'not_start_transition_fire_exception' => array(
                'expectedException' => InvalidTransitionException::notStartTransition(
                    'test_workflow',
                    'test_transition'
                ),
                'transitionExist' => true,
                'transitionAllowed' => true,
                'isTransitionStart' => false,
                'hasCurrentStep' => false,
                'stepAllowTransition' => true,
            ),
            'not_start_transition_no_exception' => array(
                'expectedResult' => false,
                'transitionExist' => true,
                'transitionAllowed' => true,
                'isTransitionStart' => false,
                'hasCurrentStep' => false,
                'stepAllowTransition' => true,
                'fireException' => false
            ),
            'step_not_allow_transition_fire_exception' => array(
                'expectedException' => InvalidTransitionException::stepHasNoAllowedTransition(
                    'test_workflow',
                    'test_step',
                    'test_transition'
                ),
                'transitionExist' => true,
                'transitionAllowed' => true,
                'isTransitionStart' => false,
                'hasCurrentStep' => true,
                'stepAllowTransition' => false,
            ),
            'step_not_allow_transition_no_exception' => array(
                'expectedResult' => false,
                'transitionExist' => true,
                'transitionAllowed' => true,
                'isTransitionStart' => false,
                'hasCurrentStep' => true,
                'stepAllowTransition' => false,
                'fireException' => false
            ),
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException
     * @expectedTransitionMessage Transition "transition" is not allowed for step "stepOne".
     */
    public function testTransitForbiddenTransition()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();
        $workflowItem->expects($this->any())
            ->method('getCurrentStepName')
            ->will($this->returnValue('stepOne'));

        $step = $this->getStepMock('stepOne');
        $step->expects($this->once())
            ->method('isAllowedTransition')
            ->with('transition')
            ->will($this->returnValue(false));

        $transition = $this->getTransitionMock('transition');

        $workflow = $this->createWorkflow();
        $workflow->getTransitionManager()->setTransitions(array($transition));
        $workflow->getStepManager()->setSteps(array($step));
        $workflow->transit($workflowItem, 'transition');
    }

    public function testTransit()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();
        $workflowItem->expects($this->any())
            ->method('getCurrentStepName')
            ->will($this->returnValue('stepOne'));
        $workflowItem->expects($this->once())
            ->method('addTransitionRecord')
            ->with($this->isInstanceOf('Oro\Bundle\WorkflowBundle\Entity\WorkflowTransitionRecord'))
            ->will(
                $this->returnCallback(
                    function (WorkflowTransitionRecord $transitionRecord) {
                        \PHPUnit_Framework_TestCase::assertEquals('transition', $transitionRecord->getTransitionName());
                        \PHPUnit_Framework_TestCase::assertEquals('stepOne', $transitionRecord->getStepFromName());
                        \PHPUnit_Framework_TestCase::assertEquals('stepTwo', $transitionRecord->getStepToName());
                    }
                )
            );

        $step = $this->getStepMock('stepOne');
        $step->expects($this->once())
            ->method('isAllowedTransition')
            ->with('transition')
            ->will($this->returnValue(true));

        $transition = $this->getTransitionMock('transition', false, 'stepTwo');
        $transition->expects($this->once())
            ->method('isAllowed')
            ->with($workflowItem)
            ->will($this->returnValue(true));
        $transition->expects($this->once())
            ->method('transit')
            ->with($workflowItem);
        $transition->expects($this->once())
            ->method('transit')
            ->with($workflowItem);

        $entityBinder = $this->getMockEntityBinder();
        $entityBinder->expects($this->once())->method('bindEntities')->with($workflowItem);

        $workflow = $this->createWorkflow();
        $workflow->getTransitionManager()->setTransitions(array($transition));
        $workflow->getStepManager()->setSteps(array($step));
        $workflow->setEntityBinder($entityBinder);
        $workflow->transit($workflowItem, 'transition');
    }

    public function testBindEntities()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $entityBinder = $this->getMockEntityBinder();
        $entityBinder->expects($this->once())->method('bindEntities')->with($workflowItem)
            ->will($this->returnValue(true));

        $workflow = $this->createWorkflow();
        $workflow->setEntityBinder($entityBinder);
        $this->assertTrue($workflow->bindEntities($workflowItem));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Entity binder is not set
     */
    public function testBindEntitiesFails()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $workflow = $this->createWorkflow();
        $workflow->bindEntities($workflowItem);
    }

    public function testSetAttributes()
    {
        $attributeOne = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')
            ->getMock();
        $attributeOne->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('attr1'));

        $attributeTwo = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')
            ->getMock();
        $attributeTwo->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('attr2'));

        $workflow = $this->createWorkflow();

        $workflow->getAttributeManager()->setAttributes(array($attributeOne, $attributeTwo));
        $attributes = $workflow->getAttributeManager()->getAttributes();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $attributes);
        $expected = array('attr1' => $attributeOne, 'attr2' => $attributeTwo);
        $this->assertEquals($expected, $attributes->toArray());

        $attributeCollection = new ArrayCollection(array('attr1' => $attributeOne, 'attr2' => $attributeTwo));
        $workflow->getAttributeManager()->setAttributes($attributeCollection);
        $attributes = $workflow->getAttributeManager()->getAttributes();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $attributes);
        $expected = array('attr1' => $attributeOne, 'attr2' => $attributeTwo);
        $this->assertEquals($expected, $attributes->toArray());
    }

    public function testGetManagedEntityAttributes()
    {
        $attributeOne = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')->getMock();
        $attributeOne->expects($this->once())->method('getName')->will($this->returnValue('attribute_one'));
        $attributeOne->expects($this->once())->method('getType')->will($this->returnValue('entity'));
        $attributeOne->expects($this->once())->method('getOption')->with('managed_entity')
            ->will($this->returnValue(true));

        $attributeTwo = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')->getMock();
        $attributeTwo->expects($this->once())->method('getType')->will($this->returnValue('entity'));
        $attributeTwo->expects($this->once())->method('getOption')->with('managed_entity')
            ->will($this->returnValue(false));

        $attributeThree = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')->getMock();
        $attributeThree->expects($this->once())->method('getName')->will($this->returnValue('attribute_three'));
        $attributeThree->expects($this->once())->method('getType')->will($this->returnValue('entity'));
        $attributeThree->expects($this->once())->method('getOption')->with('managed_entity')
            ->will($this->returnValue(true));

        $workflow = $this->createWorkflow();
        $workflow->getAttributeManager()->setAttributes(array($attributeOne, $attributeTwo, $attributeThree));

        $managedEntitiesAttributes = $workflow->getAttributeManager()->getManagedEntityAttributes();
        $this->assertEquals(2, $managedEntitiesAttributes->count());
        $this->assertEquals($attributeOne, $managedEntitiesAttributes->get('attribute_one'));
        $this->assertEquals($attributeThree, $managedEntitiesAttributes->get('attribute_three'));
    }

    public function testGetBindEntityAttributes()
    {
        $attributeOne = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')->getMock();
        $attributeOne->expects($this->once())->method('getName')->will($this->returnValue('attribute_one'));
        $attributeOne->expects($this->once())->method('getType')->will($this->returnValue('entity'));
        $attributeOne->expects($this->once())->method('getOption')->with('bind')
            ->will($this->returnValue(true));

        $attributeTwo = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')->getMock();
        $attributeTwo->expects($this->once())->method('getType')->will($this->returnValue('entity'));
        $attributeTwo->expects($this->once())->method('getOption')->with('bind')
            ->will($this->returnValue(false));

        $attributeThree = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')->getMock();
        $attributeThree->expects($this->once())->method('getName')->will($this->returnValue('attribute_three'));
        $attributeThree->expects($this->once())->method('getType')->will($this->returnValue('entity'));
        $attributeThree->expects($this->once())->method('getOption')->with('bind')
            ->will($this->returnValue(true));

        $workflow = $this->createWorkflow();
        $workflow->getAttributeManager()->setAttributes(array($attributeOne, $attributeTwo, $attributeThree));

        $bindEntitiesAttributes = $workflow->getAttributeManager()->getBindEntityAttributes();
        $this->assertEquals(2, $bindEntitiesAttributes->count());
        $this->assertEquals($attributeOne, $bindEntitiesAttributes->get('attribute_one'));
        $this->assertEquals($attributeThree, $bindEntitiesAttributes->get('attribute_three'));
    }

    public function testGetBindEntityAttributeNames()
    {
        $attributeOne = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')->getMock();
        $attributeOne->expects($this->exactly(2))->method('getName')->will($this->returnValue('attribute_one'));
        $attributeOne->expects($this->once())->method('getType')->will($this->returnValue('entity'));
        $attributeOne->expects($this->once())->method('getOption')->with('bind')
            ->will($this->returnValue(true));

        $attributeTwo = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')->getMock();
        $attributeTwo->expects($this->once())->method('getType')->will($this->returnValue('entity'));
        $attributeTwo->expects($this->once())->method('getOption')->with('bind')
            ->will($this->returnValue(false));

        $attributeThree = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')->getMock();
        $attributeThree->expects($this->exactly(2))->method('getName')->will($this->returnValue('attribute_three'));
        $attributeThree->expects($this->once())->method('getType')->will($this->returnValue('entity'));
        $attributeThree->expects($this->once())->method('getOption')->with('bind')
            ->will($this->returnValue(true));

        $workflow = $this->createWorkflow();
        $workflow->getAttributeManager()->setAttributes(array($attributeOne, $attributeTwo, $attributeThree));

        $this->assertEquals(
            array('attribute_one', 'attribute_three'),
            $workflow->getAttributeManager()->getBindEntityAttributeNames()
        );
    }

    public function testGetStepAttributes()
    {
        $attributes = new ArrayCollection();
        $workflow = $this->createWorkflow();
        $workflow->getAttributeManager()->setAttributes($attributes);
        $this->assertEquals($attributes, $workflow->getAttributeManager()->getAttributes());
    }

    public function testGetStep()
    {
        $step1 = $this->getStepMock('step1');
        $step2 = $this->getStepMock('step2');

        $workflow = $this->createWorkflow();
        $workflow->getStepManager()->setSteps(array($step1, $step2));

        $this->assertEquals($step1, $workflow->getStepManager()->getStep('step1'));
        $this->assertEquals($step2, $workflow->getStepManager()->getStep('step2'));
    }

    /**
     * @dataProvider startDataProvider
     * @param array $data
     * @param string $transitionName
     */
    public function testStart($data, $transitionName)
    {
        $transitions = array();
        if (!$transitionName) {
            $transitions[Workflow::DEFAULT_START_TRANSITION_NAME] =
                $this->getTransitionMock(Workflow::DEFAULT_START_TRANSITION_NAME, true, 'step_name');
        } else {
            $transitions[$transitionName] = $this->getTransitionMock($transitionName, true, 'step_name');
        }
        foreach ($transitions as $transition) {
            $transition->expects($this->once())
                ->method('isAllowed')
                ->will($this->returnValue(true));
        }
        $transitions = new ArrayCollection($transitions);

        $entityBinder = $this->getMockEntityBinder();
        $entityBinder->expects($this->once())
            ->method('bindEntities')
            ->with($this->isInstanceOf('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem'));

        $workflow = $this->createWorkflow();
        $workflow->getTransitionManager()->setTransitions($transitions);
        $workflow->setEntityBinder($entityBinder);
        $item = $workflow->start($data, $transitionName);
        $this->assertInstanceOf('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem', $item);
        $this->assertEquals($data, $item->getData()->getValues());
    }

    public function startDataProvider()
    {
        return array(
            array(array(), null),
            array(array('test' => 'test'), 'test')
        );
    }

    public function testGetStartTransitions()
    {
        $allowedStartTransition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Transition')
            ->disableOriginalConstructor()
            ->getMock();
        $allowedStartTransition->expects($this->once())
            ->method('isStart')
            ->will($this->returnValue(true));

        $allowedTransition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Transition')
            ->disableOriginalConstructor()
            ->getMock();
        $allowedTransition->expects($this->once())
            ->method('isStart')
            ->will($this->returnValue(false));

        $transitions = new ArrayCollection(
            array(
                $allowedStartTransition,
                $allowedTransition
            )
        );
        $expected = new ArrayCollection(array($allowedStartTransition));

        $workflow = $this->createWorkflow();
        $workflow->getTransitionManager()->setTransitions($transitions);
        $this->assertEquals($expected, $workflow->getTransitionManager()->getStartTransitions());
    }

    public function testGetAttribute()
    {
        $attribute = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')
            ->disableOriginalConstructor()
            ->getMock();
        $attributes = new ArrayCollection(array('test' => $attribute));

        $workflow = $this->createWorkflow();
        $workflow->getAttributeManager()->setAttributes($attributes);
        $this->assertSame($attribute, $workflow->getAttributeManager()->getAttribute('test'));
    }

    protected function getStepMock($name)
    {
        $step = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Step')
            ->disableOriginalConstructor()
            ->getMock();
        $step->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        return $step;
    }

    protected function getTransitionMock($name, $isStart = false, $step = null)
    {
        $transition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Transition')
            ->disableOriginalConstructor()
            ->getMock();
        $transition->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));
        if ($isStart) {
            $transition->expects($this->any())
                ->method('isStart')
                ->will($this->returnValue($isStart));
        }
        if ($step) {
            $transition->expects($this->any())
                ->method('getStepTo')
                ->will($this->returnValue($this->getStepMock($step)));
        }

        return $transition;
    }

    public function testGetAllowedTransitions()
    {
        $firstTransition = new Transition();
        $firstTransition->setName('first_transition');

        $secondTransition = new Transition();
        $secondTransition->setName('second_transition');

        $step = new Step();
        $step->setName('test_step');
        $step->setAllowedTransitions(array($secondTransition->getName()));

        $workflow = $this->createWorkflow();
        $workflow->getStepManager()->setSteps(array($step));
        $workflow->getTransitionManager()->setTransitions(array($firstTransition, $secondTransition));

        $workflowItem = new WorkflowItem();
        $workflowItem->setCurrentStepName($step->getName());

        $actualTransitions = $workflow->getTransitionsByWorkflowItem($workflowItem);
        $this->assertEquals(array($secondTransition), $actualTransitions->getValues());
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\UnknownStepException
     * @expectedExceptionMessage Step "unknown_step" not found
     */
    public function testGetAllowedTransitionsUnknownStepException()
    {
        $workflowItem = new WorkflowItem();
        $workflowItem->setCurrentStepName('unknown_step');

        $workflow = $this->createWorkflow();
        $workflow->getTransitionsByWorkflowItem($workflowItem);
    }

    public function testIsTransitionAvailable()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();
        $errors = new ArrayCollection();
        $transitionName = 'test_transition';
        $transition = $this->getTransitionMock($transitionName);
        $transition->expects($this->once())
            ->method('isAvailable')
            ->with($workflowItem, $errors)
            ->will($this->returnValue(true));
        $transitionManager = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\TransitionManager')
            ->disableOriginalConstructor()
            ->getMock();
        $transitionManager->expects($this->once())
            ->method('extractTransition')
            ->with($transition)
            ->will($this->returnValue($transition));
        $workflow = new Workflow(null, null, $transitionManager);

        $this->assertTrue($workflow->isTransitionAvailable($workflowItem, $transition, $errors));
    }

    public function testIsStartTransitionAvailable()
    {
        $data = array();
        $errors = new ArrayCollection();
        $transitionName = 'test_transition';
        $transition = $this->getTransitionMock($transitionName);
        $transition->expects($this->once())
            ->method('isAvailable')
            ->with($this->isInstanceOf('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem'), $errors)
            ->will($this->returnValue(true));
        $transitionManager = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\TransitionManager')
            ->disableOriginalConstructor()
            ->getMock();
        $transitionManager->expects($this->once())
            ->method('extractTransition')
            ->with($transition)
            ->will($this->returnValue($transition));
        $workflow = new Workflow(null, null, $transitionManager);

        $this->assertTrue($workflow->isStartTransitionAvailable($transition, $data, $errors));
    }

    /**
     * @param null|string $workflowName
     * @return Workflow
     */
    protected function createWorkflow($workflowName = null)
    {
        $workflow = new Workflow();
        $workflow->setName($workflowName);
        return $workflow;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockEntityBinder()
    {
        return $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\EntityBinder')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
