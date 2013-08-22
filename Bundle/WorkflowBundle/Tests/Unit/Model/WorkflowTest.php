<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\Workflow;

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
            'startStepName' => array('startStepName', 'current_step'),
            'managedEntityClass' => array('managedEntityClass', 'Test\Bundle\FooBundle\Entity\Bar')
        );
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
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $workflow->getSteps());
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

        $workflow->setSteps(array($stepOne, $stepTwo));
        $steps = $workflow->getSteps();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $steps);
        $expected = array('step1' => $stepOne, 'step2' => $stepTwo);
        $this->assertEquals($expected, $steps->toArray());

        $stepsCollection = new ArrayCollection(array('step1' => $stepOne, 'step2' => $stepTwo));
        $workflow->setSteps($stepsCollection);
        $steps = $workflow->getSteps();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $steps);
        $expected = array('step1' => $stepOne, 'step2' => $stepTwo);
        $this->assertEquals($expected, $steps->toArray());
    }

    public function testGetTransitionsEmpty()
    {
        $workflow = $this->createWorkflow();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $workflow->getTransitions());
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

        $workflow->setTransitions(array($transitionOne, $transitionTwo));
        $transitions = $workflow->getTransitions();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $transitions);
        $expected = array('transition1' => $transitionOne, 'transition2' => $transitionTwo);
        $this->assertEquals($expected, $transitions->toArray());

        $transitionsCollection = new ArrayCollection(
            array('transition1' => $transitionOne, 'transition2' => $transitionTwo)
        );
        $workflow->setTransitions($transitionsCollection);
        $transitions = $workflow->getTransitions();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $transitions);
        $expected = array('transition1' => $transitionOne, 'transition2' => $transitionTwo);
        $this->assertEquals($expected, $transitions->toArray());
    }

    public function testCreateWorkflow()
    {
        $data = array('name' => 'value');
        $workflow = $this->createWorkflow();
        $workflow->setStartStepName('startStep');
        $workflow->setName('testWorkflow');

        $workflowItem = $workflow->createWorkflowItem($data);
        $this->assertInstanceOf('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem', $workflowItem);
        $this->assertEquals('startStep', $workflowItem->getCurrentStepName());
        $this->assertEquals('testWorkflow', $workflowItem->getWorkflowName());
        $this->assertEquals($data['name'], $workflowItem->getData()->get('name'));
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

    public function testIsTransitionAllowedUnknownTransition()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $workflow = $this->createWorkflow();
        $this->assertFalse($workflow->isTransitionAllowed($workflowItem, 'test'));
    }

    /**
     * @dataProvider isAllowedDataProvider
     * @param bool $isAllowed
     */
    public function testIsTransitionAllowedTransition($isAllowed)
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $transition = $this->getTransitionMock('transition');
        $transition->expects($this->exactly(2))
            ->method('isAllowed')
            ->will($this->returnValue($isAllowed));

        $workflow = $this->createWorkflow();
        $workflow->setTransitions(array($transition));

        $this->assertEquals($isAllowed, $workflow->isTransitionAllowed($workflowItem, 'transition'));
        $this->assertEquals($isAllowed, $workflow->isTransitionAllowed($workflowItem, $transition));
    }

    /**
     * @return array
     */
    public function isAllowedDataProvider()
    {
        return array(
            'yes' => array(true),
            'no' => array(false)
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\UnknownTransitionException
     * @expectedExceptionMessage Unknown transition "unknown".
     */
    public function testTransitUnknownTransitionException()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $workflow = $this->createWorkflow();
        $workflow->transit($workflowItem, 'unknown');
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\UnknownStepException
     * @expectedExceptionMessage Step "unknownStep" not found
     */
    public function testTransitUnknownStepException()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();
        $workflowItem->expects($this->any())
            ->method('getCurrentStepName')
            ->will($this->returnValue('unknownStep'));
        $transition = $this->getTransitionMock('transition');

        $workflow = $this->createWorkflow();
        $workflow->setName('workflowName');
        $workflow->setTransitions(array($transition));
        $workflow->transit($workflowItem, $transition);
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

        $transition = $this->getTransitionMock('transition');
        $step = $this->getStepMock('stepOne');
        $step->expects($this->once())
            ->method('isAllowedTransition')
            ->with('transition')
            ->will($this->returnValue(false));

        $workflow = $this->createWorkflow();
        $workflow->setTransitions(array($transition));
        $workflow->setSteps(array($step));
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

        $transition = $this->getTransitionMock('transition');
        $transition->expects($this->once())
            ->method('transit')
            ->with($workflowItem);

        $step = $this->getStepMock('stepOne');
        $step->expects($this->once())
            ->method('isAllowedTransition')
            ->with('transition')
            ->will($this->returnValue(true));

        $workflow = $this->createWorkflow();
        $workflow->setTransitions(array($transition));
        $workflow->setSteps(array($step));
        $workflow->transit($workflowItem, 'transition');
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

        $workflow->setAttributes(array($attributeOne, $attributeTwo));
        $attributes = $workflow->getAttributes();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $attributes);
        $expected = array('attr1' => $attributeOne, 'attr2' => $attributeTwo);
        $this->assertEquals($expected, $attributes->toArray());

        $attributeCollection = new ArrayCollection(array('attr1' => $attributeOne, 'attr2' => $attributeTwo));
        $workflow->setAttributes($attributeCollection);
        $attributes = $workflow->getAttributes();
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
        $workflow->setAttributes(array($attributeOne, $attributeTwo, $attributeThree));

        $managedEntitiesAttributes = $workflow->getManagedEntityAttributes();
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
        $workflow->setAttributes(array($attributeOne, $attributeTwo, $attributeThree));

        $bindEntitiesAttributes = $workflow->getBindEntityAttributes();
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
        $workflow->setAttributes(array($attributeOne, $attributeTwo, $attributeThree));

        $this->assertEquals(array('attribute_one', 'attribute_three'), $workflow->getBindEntityAttributeNames());
    }

    public function testGetStepAttributes()
    {
        $attributes = new ArrayCollection();
        $workflow = $this->createWorkflow();
        $workflow->setAttributes($attributes);
        $this->assertEquals($attributes, $workflow->getAttributes());
    }

    public function testGetStep()
    {
        $step1 = $this->getStepMock('step1');
        $step2 = $this->getStepMock('step2');

        $workflow = $this->createWorkflow();
        $workflow->setSteps(array($step1, $step2));

        $this->assertEquals($step1, $workflow->getStep('step1'));
        $this->assertEquals($step2, $workflow->getStep('step2'));
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\UnknownStepException
     * @expectedExceptionMessage Step "unknown_step" not found
     */
    public function testGetStepUnknownStep()
    {
        $workflow = $this->createWorkflow();
        $workflow->setSteps(array());
        $workflow->getStep('unknown_step');
    }

    public function testGetStartStep()
    {
        $startStepName = 'start_step';
        $startStep = $this->getStepMock($startStepName);

        $workflow = $this->createWorkflow();
        $workflow->setStartStepName($startStepName);
        $workflow->setSteps(array($startStep));

        $this->assertEquals($startStep, $workflow->getStartStep());
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

    protected function getTransitionMock($name)
    {
        $transition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Transition')
            ->disableOriginalConstructor()
            ->getMock();
        $transition->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));
        return $transition;
    }

    /**
     * @param array $data
     * @return Workflow
     */
    protected function createWorkflow(array $data = array())
    {
        return new Workflow($data);
    }
}
