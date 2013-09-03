<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\PostAction\RedirectToWorkflow;

class RedirectToWorkflowTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $parentPostAction;

    /**
     * @var RedirectToWorkflow
     */
    protected $postAction;

    protected function setUp()
    {
        $this->parentPostAction = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\Redirect')
            ->disableOriginalConstructor()
            ->setMethods(array('execute', 'initialize', 'setCondition'))
            ->getMock();

        $this->postAction = new RedirectToWorkflow($this->parentPostAction);
    }

    protected function tearDown()
    {
        unset($this->parentPostAction);
        unset($this->postAction);
    }

    public function testInitialize()
    {
        $inputOptions = array(
            'workflow_item' => new PropertyPath('data.workflow_item')
        );
        $expectedOptions = array_merge(
            $inputOptions,
            array(
                'route' => 'oro_workflow_step_edit',
                'route_parameters' => array(
                    'id' => new PropertyPath('data.workflow_item.id')
                ),
            )
        );

        $this->parentPostAction->expects($this->once())
            ->method('initialize')
            ->with($expectedOptions);

        $this->postAction->initialize($inputOptions);
    }

    /**
     * @param array $options
     * @param string $exceptionName
     * @param string $exceptionMessage
     * @dataProvider initializeExceptionDataProvider
     */
    public function testInitializeException(array $options, $exceptionName, $exceptionMessage)
    {
        $this->setExpectedException($exceptionName, $exceptionMessage);
        $this->postAction->initialize($options);
    }

    /**
     * @return array
     */
    public function initializeExceptionDataProvider()
    {
        return array(
            'no workflow item' => array(
                'options' => array(),
                'exceptionName' => '\Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'exceptionMessage' => 'Workflow item parameter is required',
            ),
            'incorrect workflow item' => array(
                'options' => array(
                    'workflow_item' => 'stringData'
                ),
                'exceptionName' => '\Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'exceptionMessage' => 'Workflow item must be valid property definition',
            ),
        );
    }

    public function testExecute()
    {
        $context = array();
        $this->parentPostAction->expects($this->once())
            ->method('execute')
            ->with($context);
        $this->postAction->execute($context);
    }

    public function testSetCondition()
    {
        $condition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->parentPostAction->expects($this->once())
            ->method('setCondition')
            ->with($condition);

        $this->postAction->setCondition($condition);
    }
}
