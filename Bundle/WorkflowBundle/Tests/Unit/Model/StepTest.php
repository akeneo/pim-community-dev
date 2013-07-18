<?php
namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Oro\Bundle\WorkflowBundle\Model\Step;

class StepTest extends \PHPUnit_Framework_TestCase
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
        $obj = new Step();
        $this->assertInstanceOf(
            'Oro\Bundle\WorkflowBundle\Model\Step',
            call_user_func_array(array($obj, $setter), array($value))
        );
        $this->assertEquals($value, call_user_func_array(array($obj, $getter), array()));
    }

    public function propertiesDataProvider()
    {
        return array(
            'name' => array('name', 'test'),
            'order' => array('order', 1),
            'template' => array('template', 'test'),
            'allowedTransitions' => array('allowedTransitions', array('one', 'two')),
        );
    }

    public function testIsFinal()
    {
        $obj = new Step();
        $this->assertFalse($obj->isFinal());
        $obj->setIsFinal(true);
        $this->assertTrue($obj->isFinal());
        $obj->setIsFinal(false);
        $this->assertFalse($obj->isFinal());
    }

    public function testAllowTransition()
    {
        $obj = new Step();

        $this->assertFalse($obj->hasAllowedTransitions());
        $obj->allowTransition('test');
        $this->assertTrue($obj->hasAllowedTransitions());
        $this->assertEquals(array('test'), $obj->getAllowedTransitions(), 'Transition was not allowed');

        // Check duplicate
        $obj->allowTransition('test');
        $this->assertEquals(array('test'), $obj->getAllowedTransitions(), 'Transition was allowed more than once');

        // Check allowing more than one transition
        $obj->allowTransition('test2');
        $this->assertEquals(
            array('test', 'test2'),
            $obj->getAllowedTransitions(),
            'Second transition was not allowed'
        );

        // Check disallow
        $obj->disallowTransition('test2');
        $this->assertEquals(array('test'), $obj->getAllowedTransitions(), 'Transition was not disallowed');

        // Check isAllowed
        $this->assertTrue($obj->isAllowedTransition('test'), 'Expected transition not allowed');
        $this->assertFalse($obj->isAllowedTransition('test2'), 'Unexpected transition allowed');
    }

    public function testGetAttributesEmpty()
    {
        $obj = new Step();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $obj->getAttributes());
    }

    public function testSetAttributes()
    {
        $stepAttributeOne = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\StepAttribute')
            ->getMock();
        $stepAttributeOne->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('attr1'));

        $stepAttributeTwo = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\StepAttribute')
            ->getMock();
        $stepAttributeTwo->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('attr2'));

        $obj = new Step();
        $obj->setAttributes(array($stepAttributeOne, $stepAttributeTwo));
        $attributes = $obj->getAttributes();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $attributes);
        $expected = array('attr1' => $stepAttributeOne, 'attr2' => $stepAttributeTwo);
        $this->assertEquals($expected, $attributes->toArray());
    }
}
