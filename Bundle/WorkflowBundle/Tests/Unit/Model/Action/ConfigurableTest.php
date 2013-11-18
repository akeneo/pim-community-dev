<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action;

use Oro\Bundle\WorkflowBundle\Model\Action\Configurable;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configurable
     */
    protected $configurableAction;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $assembler;

    /**
     * @var array
     */
    protected $testConfiguration = array('key' => 'value');

    /**
     * @var array
     */
    protected $testContext = array(1, 2, 3);

    protected function setUp()
    {
        $this->assembler = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Action\ActionAssembler')
            ->disableOriginalConstructor()
            ->setMethods(array('assemble'))
            ->getMock();
        $this->configurableAction = new Configurable($this->assembler);
    }

    protected function tearDown()
    {
        unset($this->configurableAction);
        unset($this->assembler);
    }

    public function testInitialize()
    {
        $this->assertAttributeEmpty('configuration', $this->configurableAction);
        $this->configurableAction->initialize($this->testConfiguration);
        $this->assertAttributeEquals($this->testConfiguration, 'configuration', $this->configurableAction);
    }

    public function testExecute()
    {
        $action = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $action->expects($this->exactly(2))
            ->method('execute')
            ->with($this->testContext);

        $condition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $condition->expects($this->never())
            ->method('isAllowed');

        $this->assembler->expects($this->once())
            ->method('assemble')
            ->with($this->testConfiguration)
            ->will($this->returnValue($action));

        $this->configurableAction->initialize($this->testConfiguration);
        $this->configurableAction->setCondition($condition);

        // run twice to test cached action
        $this->configurableAction->execute($this->testContext);
        $this->configurableAction->execute($this->testContext);
    }
}
