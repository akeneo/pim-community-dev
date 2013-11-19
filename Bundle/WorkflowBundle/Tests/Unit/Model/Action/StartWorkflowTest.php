<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\Action\StartWorkflow;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\ItemStub;

class StartWorkflowTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StartWorkflow
     */
    protected $action;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|
     */
    protected $workflowManager;

    protected function setUp()
    {
        $this->workflowManager = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\WorkflowManager')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();

        $this->action = new StartWorkflow(new ContextAccessor(), $this->workflowManager);
    }

    protected function tearDown()
    {
        unset($this->workflowManager);
        unset($this->action);
    }

    /**
     * @param array $options
     * @dataProvider optionsDataProvider
     */
    public function testInitialize(array $options)
    {
        $this->action->initialize($options);
        $this->assertAttributeEquals($options, 'options', $this->action);
    }

    public function optionsDataProvider()
    {
        $workflowItem = $this->createWorkflowItem();

        $actualContext = new ItemStub(
            array(
                'workflowName' => 'acmeWorkflow',
                'entityValue' => new \DateTime('now'),
                'startTransition' => 'acmeStartTransition',
                'someKey' => 'someValue'
            )
        );

        $expectedContext = clone $actualContext;
        $expectedContext->workflowItem = $workflowItem;

        return array(
            'minimum options' => array(
                'options' => array(
                    'name' => $actualContext->workflowName,
                    'attribute' => new PropertyPath('workflowItem'),
                ),
                'actualContext' => $actualContext,
                'expectedContext' => $expectedContext,
            ),
            'maximum plain option' => array(
                'options' => array(
                    'name' => $actualContext->workflowName,
                    'attribute' => new PropertyPath('workflowItem'),
                    'entity' => new PropertyPath('entityValue'),
                    'transition' => $actualContext->startTransition,
                    'data' => array(
                        'plainData' => 'plainDataValue',
                    )
                ),
                'actualContext' => $actualContext,
                'expectedContext' => $expectedContext,
                'expectedData' => array(
                    'plainData' => 'plainDataValue',
                )
            ),
            'maximum property path options' => array(
                'options' => array(
                    'name' => new PropertyPath('workflowName'),
                    'attribute' => new PropertyPath('workflowItem'),
                    'entity' => new PropertyPath('entityValue'),
                    'transition' => new PropertyPath('startTransition'),
                    'data' => array(
                        'propertyData' => new PropertyPath('someKey'),
                    ),
                ),
                'actualContext' => $actualContext,
                'expectedContext' => $expectedContext,
                'expectedData' => array(
                    'propertyData' => $expectedContext->someKey,
                ),
            ),
        );
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
        $this->action->initialize($options);
    }

    /**
     * @return array
     */
    public function initializeExceptionDataProvider()
    {
        return array(
            'no name' => array(
                'options' => array(),
                'exceptionName' => '\Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'exceptionMessage' => 'Workflow name parameter is required',
            ),
            'no attribute' => array(
                'options' => array(
                    'name' => 'acmeWorkflow'
                ),
                'exceptionName' => '\Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'exceptionMessage' => 'Attribute name parameter is required',
            ),
            'invalid attribute' => array(
                'options' => array(
                    'name' => 'acmeWorkflow',
                    'attribute' => 'notPropertyPath'
                ),
                'exceptionName' => '\Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'exceptionMessage' => 'Attribute must be valid property definition',
            ),
            'invalid entity' => array(
                'options' => array(
                    'name' => 'acmeWorkflow',
                    'attribute' => new PropertyPath('workflowItem'),
                    'entity' => 'notPropertyPath'
                ),
                'exceptionName' => '\Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'exceptionMessage' => 'Entity must be valid property definition',
            ),
        );
    }

    /**
     * @param array $options
     * @param ItemStub $actualContext
     * @param ItemStub $expectedContext
     * @param array $expectedData
     * @dataProvider optionsDataProvider
     */
    public function testExecute(
        array $options,
        ItemStub $actualContext,
        ItemStub $expectedContext,
        array $expectedData = array()
    ) {
        $expectedWorkflowName = $expectedContext->workflowName;
        $expectedEntity = !empty($options['entity']) ? $expectedContext->entityValue : null;
        $expectedTransition = !empty($options['transition']) ? $expectedContext->startTransition : null;
        $expectedWorkflowItem = $expectedContext->workflowItem;

        $this->workflowManager->expects($this->once())
            ->method('startWorkflow')
            ->with($expectedWorkflowName, $expectedEntity, $expectedTransition, $expectedData)
            ->will($this->returnValue($expectedWorkflowItem));

        $this->action->initialize($options);
        $this->action->execute($actualContext);

        $this->assertEquals($expectedContext->getData(), $actualContext->getData());
    }

    /**
     * @return WorkflowItem
     */
    protected function createWorkflowItem()
    {
        $workflowItem = new WorkflowItem();
        $workflowItem->setId(1);

        return $workflowItem;
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Entity value must be an object
     */
    public function testExecuteEntityNotAnObject()
    {
        $options = array(
            'name' => 'acmeWorkflow',
            'attribute' => new PropertyPath('workflowItem'),
            'entity' => new PropertyPath('entityValue'),
        );
        $context = new ItemStub(
            array(
                'workflowName' => 'acmeWorkflow',
                'entityValue' => 'notAnObject',
            )
        );

        $this->action->initialize($options);
        $this->action->execute($context);

    }
}
