<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Serializer;

use Oro\Bundle\WorkflowBundle\Serializer\WorkflowDataSerializer;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

class WorkflowDataSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WorkflowDataSerializer
     */
    protected $serializer;

    protected function setUp()
    {
        $this->serializer = new WorkflowDataSerializer();
    }

    protected function tearDown()
    {
        unset($this->serializer);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createWorkflowRegistryMock()
    {
        $workflowRegistry = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry')
            ->disableOriginalConstructor()
            ->setMethods(array('getWorkflow'))
            ->getMock();

        return $workflowRegistry;
    }

    public function testSetWorkflowRegistry()
    {
        /** @var WorkflowRegistry $workflowRegistry */
        $workflowRegistry = $this->createWorkflowRegistryMock();
        $this->serializer->setWorkflowRegistry($workflowRegistry);
        $this->assertAttributeEquals($workflowRegistry, 'workflowRegistry', $this->serializer);
    }

    public function testSetWorkflowName()
    {
        $this->assertAttributeEmpty('workflowName', $this->serializer);
        $workflowName = 'test_workflow';
        $this->serializer->setWorkflowName($workflowName);
        $this->assertAttributeEquals($workflowName, 'workflowName', $this->serializer);
    }

    public function testGetWorkflowName()
    {
        $this->assertNull($this->serializer->getWorkflowName());
        $workflowName = 'test_workflow';
        $this->serializer->setWorkflowName($workflowName);
        $this->assertEquals($workflowName, $this->serializer->getWorkflowName());
    }

    public function testGetWorkflow()
    {
        $workflowName = 'test_workflow';
        /** @var Workflow $workflow */
        $workflow = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Workflow')
            ->disableOriginalConstructor()
            ->getMock();

        $workflowRegistry = $this->createWorkflowRegistryMock();
        $workflowRegistry->expects($this->once())
            ->method('getWorkflow')
            ->with($workflowName)
            ->will($this->returnValue($workflow));

        /** @var WorkflowRegistry $workflowRegistry */
        $this->serializer->setWorkflowRegistry($workflowRegistry);
        $this->serializer->setWorkflowName($workflowName);
        $this->assertEquals($workflow, $this->serializer->getWorkflow());
    }
}
