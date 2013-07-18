<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Oro\Bundle\WorkflowBundle\Model\Workflow;

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
        $obj = new Workflow();
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
            'startStep' => array('startStep', $this->getMock('Oro\Bundle\WorkflowBundle\Model\Step')),
            'managedEntityType' => array('managedEntityType', 'Test\Bundle\FooBundle\Entity\Bar')
        );
    }

    public function testEnabled()
    {
        $obj = new Workflow();
        $this->assertTrue($obj->isEnabled());

        $obj->setEnabled(false);
        $this->assertFalse($obj->isEnabled());

        $obj->setEnabled(true);
        $this->assertTrue($obj->isEnabled());
    }

    public function testGetStepsEmpty()
    {
        $obj = new Workflow();
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

        $obj = new Workflow();
        $obj->setSteps(array($stepOne, $stepTwo));
        $steps = $obj->getSteps();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $steps);
        $expected = array('step1' => $stepOne, 'step2' => $stepTwo);
        $this->assertEquals($expected, $steps->toArray());
    }

    public function testGetTransitionsEmpty()
    {
        $obj = new Workflow();
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

        $obj = new Workflow();
        $obj->setTransitions(array($transitionOne, $transitionTwo));
        $transitions = $obj->getTransitions();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $transitions);
        $expected = array('transition1' => $transitionOne, 'transition2' => $transitionTwo);
        $this->assertEquals($expected, $transitions->toArray());
    }
}
