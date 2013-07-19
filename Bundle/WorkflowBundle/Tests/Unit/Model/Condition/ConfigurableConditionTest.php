<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition\ConfigurableCondition;

class ConfigurableConditionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $assembler;

    /**
     * @var ConfigurableCondition
     */
    protected $condition;

    protected function setUp()
    {
        $this->assembler = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionAssembler')
            ->disableOriginalConstructor()
            ->getMock();
        $this->condition = new ConfigurableCondition($this->assembler);
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

        $realCondition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface')
            ->getMockForAbstractClass();
        $realCondition->expects($this->exactly(2))
            ->method('isAllowed')
            ->will($this->returnValue(true));

        $this->assembler->expects($this->once())
            ->method('assemble')
            ->with($options)
            ->will($this->returnValue($realCondition));

        $this->condition->initialize($options);
        $this->assertTrue($this->condition->isAllowed($workflowItem));
        $this->assertTrue($this->condition->isAllowed($workflowItem));
    }
}
