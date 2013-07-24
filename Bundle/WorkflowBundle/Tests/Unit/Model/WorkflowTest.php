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
     * @param array $parameters
     * @return Workflow
     */
    protected function createWorkflow(array $parameters = array())
    {
        $entityBinder = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\EntityBinder')
            ->disableOriginalConstructor()
            ->getMock();

        $defaultParameters = array(
            'entityBinder' => $entityBinder
        );

        $parameters = array_merge($defaultParameters, $parameters);

        return new Workflow($parameters['entityBinder']);
    }

    /**
     * @dataProvider propertiesDataProvider
     * @param string $property
     * @param mixed $value
     */
    public function testGettersAndSetters($property, $value)
    {
        $getter = 'get' . ucfirst($property);
        $setter = 'set' . ucfirst($property);
        $obj = $this->createWorkflow();
        $this->assertInstanceOf(
            'Oro\Bundle\WorkflowBundle\Model\Workflow',
            call_user_func_array(array($obj, $setter), array($value))
        );
        $this->assertEquals($value, call_user_func_array(array($obj, $getter), array()));
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
        $obj = $this->createWorkflow();
        $this->assertTrue($obj->isEnabled());

        $obj->setEnabled(false);
        $this->assertFalse($obj->isEnabled());

        $obj->setEnabled(true);
        $this->assertTrue($obj->isEnabled());
    }

    public function testGetStepsEmpty()
    {
        $obj = $this->createWorkflow();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $obj->getSteps());
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

        $obj = $this->createWorkflow();

        $obj->setSteps(array($stepOne, $stepTwo));
        $steps = $obj->getSteps();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $steps);
        $expected = array('step1' => $stepOne, 'step2' => $stepTwo);
        $this->assertEquals($expected, $steps->toArray());

        $stepsCollection = new ArrayCollection(array('step1' => $stepOne, 'step2' => $stepTwo));
        $obj->setSteps($stepsCollection);
        $steps = $obj->getSteps();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $steps);
        $expected = array('step1' => $stepOne, 'step2' => $stepTwo);
        $this->assertEquals($expected, $steps->toArray());
    }

    public function testGetTransitionsEmpty()
    {
        $obj = $this->createWorkflow();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $obj->getTransitions());
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

        $obj = $this->createWorkflow();

        $obj->setTransitions(array($transitionOne, $transitionTwo));
        $transitions = $obj->getTransitions();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $transitions);
        $expected = array('transition1' => $transitionOne, 'transition2' => $transitionTwo);
        $this->assertEquals($expected, $transitions->toArray());

        $transitionsCollection = new ArrayCollection(
            array('transition1' => $transitionOne, 'transition2' => $transitionTwo)
        );
        $obj->setTransitions($transitionsCollection);
        $transitions = $obj->getTransitions();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $transitions);
        $expected = array('transition1' => $transitionOne, 'transition2' => $transitionTwo);
        $this->assertEquals($expected, $transitions->toArray());
    }

    /**
     * @param object|null $entity
     * @dataProvider createWorkflowDataProvider
     */
    public function testCreateWorkflow($entity)
    {
        $entityBinder = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\EntityBinder')
            ->disableOriginalConstructor()
            ->setMethods(array('bind'))
            ->getMock();
        if ($entity) {
            $entityBinder->expects($this->once())
                ->method('bind')
                ->with($this->isInstanceOf('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem'), $entity);
        } else {
            $entityBinder->expects($this->never())
                ->method('bind');
        }

        $obj = $this->createWorkflow(array('entityBinder' => $entityBinder));
        $obj->setStartStepName('startStep');
        $obj->setName('testWorkflow');
        if ($entity) {
            $obj->setManagedEntityClass(get_class($entity));
        }

        $workflowItem = $obj->createWorkflowItem($entity);
        $this->assertInstanceOf('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem', $workflowItem);
        $this->assertEquals('startStep', $workflowItem->getCurrentStepName());
        $this->assertEquals('testWorkflow', $workflowItem->getWorkflowName());
        if ($entity) {
            $this->assertEquals($entity, $workflowItem->getData()->get(Workflow::MANAGED_ENTITY_KEY));
        }
    }

    /**
     * @return array
     */
    public function createWorkflowDataProvider()
    {
        return array(
            'without entity' => array(
                'entity' => null,
            ),
            'with entity' => array(
                'entity' => new \DateTime('now'),
            )
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Managed entity must exist
     */
    public function testCreateWorkflowNoEntityException()
    {
        $obj = $this->createWorkflow();
        $obj->setManagedEntityClass('\DateTime');
        $obj->createWorkflowItem();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Managed entity must be instance of \DateTime
     */
    public function testCreateWorkflowIncorrectEntityException()
    {
        $obj = $this->createWorkflow();
        $obj->setManagedEntityClass('\DateTime');
        $obj->createWorkflowItem(new \DateTimeZone('UTC'));
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

        $obj = $this->createWorkflow();
        $obj->isTransitionAllowed($workflowItem, 1);
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

        $obj = $this->createWorkflow();
        $obj->transit($workflowItem, 1);
    }

    public function testIsTransitionAllowedUnknownTransition()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->createWorkflow();
        $this->assertFalse($obj->isTransitionAllowed($workflowItem, 'test'));
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

        $obj = $this->createWorkflow();
        $obj->setTransitions(array($transition));

        $this->assertEquals($isAllowed, $obj->isTransitionAllowed($workflowItem, 'transition'));
        $this->assertEquals($isAllowed, $obj->isTransitionAllowed($workflowItem, $transition));
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

        $obj = $this->createWorkflow();
        $obj->transit($workflowItem, 'unknown');
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

        $obj = $this->createWorkflow();
        $obj->setName('workflowName');
        $obj->setTransitions(array($transition));
        $obj->transit($workflowItem, $transition);
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

        $obj = $this->createWorkflow();
        $obj->setTransitions(array($transition));
        $obj->setSteps(array($step));
        $obj->transit($workflowItem, 'transition');
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

        $obj = $this->createWorkflow();
        $obj->setTransitions(array($transition));
        $obj->setSteps(array($step));
        $obj->transit($workflowItem, 'transition');
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

        $obj = $this->createWorkflow();

        $obj->setAttributes(array($attributeOne, $attributeTwo));
        $attributes = $obj->getAttributes();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $attributes);
        $expected = array('attr1' => $attributeOne, 'attr2' => $attributeTwo);
        $this->assertEquals($expected, $attributes->toArray());

        $attributeCollection = new ArrayCollection(array('attr1' => $attributeOne, 'attr2' => $attributeTwo));
        $obj->setAttributes($attributeCollection);
        $attributes = $obj->getAttributes();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $attributes);
        $expected = array('attr1' => $attributeOne, 'attr2' => $attributeTwo);
        $this->assertEquals($expected, $attributes->toArray());
    }

    public function testGetStepAttributes()
    {
        $attributes = new ArrayCollection();
        $obj = $this->createWorkflow();
        $obj->setAttributes($attributes);
        $this->assertEquals($attributes, $obj->getAttributes());
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
}
