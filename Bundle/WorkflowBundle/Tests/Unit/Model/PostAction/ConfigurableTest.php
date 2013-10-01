<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Model\PostAction\Configurable;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configurable
     */
    protected $configurablePostAction;

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
        $this->assembler = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionAssembler')
            ->disableOriginalConstructor()
            ->setMethods(array('assemble'))
            ->getMock();
        $this->configurablePostAction = new Configurable($this->assembler);
    }

    protected function tearDown()
    {
        unset($this->configurablePostAction);
        unset($this->assembler);
    }

    public function testInitialize()
    {
        $this->assertAttributeEmpty('configuration', $this->configurablePostAction);
        $this->configurablePostAction->initialize($this->testConfiguration);
        $this->assertAttributeEquals($this->testConfiguration, 'configuration', $this->configurablePostAction);
    }

    public function testExecute()
    {
        $postAction = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $postAction->expects($this->exactly(2))
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
            ->will($this->returnValue($postAction));

        $this->configurablePostAction->initialize($this->testConfiguration);
        $this->configurablePostAction->setCondition($condition);

        // run twice to test cached post action
        $this->configurablePostAction->execute($this->testContext);
        $this->configurablePostAction->execute($this->testContext);
    }
}
