<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition\Configurable;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $assembler;

    /**
     * @var Configurable
     */
    protected $condition;

    protected function setUp()
    {
        $this->assembler = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionAssembler')
            ->disableOriginalConstructor()
            ->getMock();
        $this->condition = new Configurable($this->assembler);
    }

    public function testInitialize()
    {
        $this->assertInstanceOf(
            'Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface',
            $this->condition->initialize(array())
        );
    }

    public function testIsAllowed()
    {
        $options = array();

        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();
        $errors = $this->getMockForAbstractClass('Doctrine\Common\Collections\Collection');

        $realCondition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface')
            ->getMockForAbstractClass();
        $realCondition->expects($this->exactly(2))
            ->method('isAllowed')
            ->with($workflowItem, $errors)
            ->will($this->returnValue(true));

        $this->assembler->expects($this->once())
            ->method('assemble')
            ->with($options)
            ->will($this->returnValue($realCondition));

        $this->condition->initialize($options);
        $this->assertTrue($this->condition->isAllowed($workflowItem, $errors));
        $this->assertTrue($this->condition->isAllowed($workflowItem, $errors));
    }
}
