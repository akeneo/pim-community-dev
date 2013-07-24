<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Symfony\Bridge\Doctrine\ManagerRegistry;

use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowAssembler;

class WorkflowRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createWorkflowDefinitionRepositoryMock()
    {
        $workflowDefinitionRepository
            = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowDefinitionRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('find', 'findWorkflowDefinitionsByEntity'))
            ->getMock();

        return $workflowDefinitionRepository;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject
     * @return ManagerRegistry
     */
    protected function createManagerRegistryMock($workflowDefinitionRepository)
    {
        $managerRegistry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository'))
            ->getMockForAbstractClass();
        $managerRegistry->expects($this->once())
            ->method('getRepository')
            ->with('OroWorkflowBundle:WorkflowDefinition')
            ->will($this->returnValue($workflowDefinitionRepository));

        return $managerRegistry;
    }

    /**
     * @param WorkflowDefinition|null $workflowDefinition
     * @param Workflow|null $workflow
     * @return WorkflowAssembler
     */
    public function createWorkflowAssemblerMock($workflowDefinition = null, $workflow = null)
    {
        $workflowAssembler = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\WorkflowAssembler')
            ->disableOriginalConstructor()
            ->setMethods(array('assemble'))
            ->getMock();
        if ($workflowDefinition && $workflow) {
            $workflowAssembler->expects($this->once())
                ->method('assemble')
                ->with($workflowDefinition)
                ->will($this->returnValue($workflow));
        } else {
            $workflowAssembler->expects($this->never())
                ->method('assemble');
        }

        return $workflowAssembler;
    }

    public function testGetWorkflow()
    {
        $workflowName = 'test_workflow';
        $workflowDefinition = new WorkflowDefinition();
        $workflowDefinition->setName($workflowName);
        /** @var Workflow $workflow */
        $workflow = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Workflow')
            ->disableOriginalConstructor()
            ->getMock();

        $workflowDefinitionRepository = $this->createWorkflowDefinitionRepositoryMock();
        $workflowDefinitionRepository->expects($this->once())
            ->method('find')
            ->with($workflowName)
            ->will($this->returnValue($workflowDefinition));
        $managerRegistry = $this->createManagerRegistryMock($workflowDefinitionRepository);
        $workflowAssembler = $this->createWorkflowAssemblerMock($workflowDefinition, $workflow);

        $workflowRegistry = new WorkflowRegistry($managerRegistry, $workflowAssembler);
        // run twice to test cache storage inside registry
        $this->assertEquals($workflow, $workflowRegistry->getWorkflow($workflowName));
        $this->assertEquals($workflow, $workflowRegistry->getWorkflow($workflowName));
        $this->assertAttributeEquals(array($workflowName => $workflow), 'workflowByName', $workflowRegistry);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException
     * @expectedExceptionMessage Workflow "not_existing_workflow" not found
     */
    public function testGetWorkflowNotFoundException()
    {
        $workflowName = 'not_existing_workflow';

        $workflowDefinitionRepository = $this->createWorkflowDefinitionRepositoryMock();
        $workflowDefinitionRepository->expects($this->once())
            ->method('find')
            ->with($workflowName)
            ->will($this->returnValue(null));
        $managerRegistry = $this->createManagerRegistryMock($workflowDefinitionRepository);
        $workflowAssembler = $this->createWorkflowAssemblerMock();

        $workflowRegistry = new WorkflowRegistry($managerRegistry, $workflowAssembler);
        $workflowRegistry->getWorkflow($workflowName);
    }

    public function testGetWorkflowsByEntity()
    {
        $entity = new \DateTime('now');
        $workflowName = 'test_workflow';
        $workflowDefinition = new WorkflowDefinition();
        $workflowDefinition->setName($workflowName);
        /** @var Workflow $workflow */
        $workflow = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Workflow')
            ->disableOriginalConstructor()
            ->getMock();

        $workflowDefinitionRepository = $this->createWorkflowDefinitionRepositoryMock();
        $workflowDefinitionRepository->expects($this->once())
            ->method('findWorkflowDefinitionsByEntity')
            ->with($entity)
            ->will($this->returnValue(array($workflowDefinition)));
        $managerRegistry = $this->createManagerRegistryMock($workflowDefinitionRepository);
        $workflowAssembler = $this->createWorkflowAssemblerMock($workflowDefinition, $workflow);

        $workflowRegistry = new WorkflowRegistry($managerRegistry, $workflowAssembler);
        $expectedWorkflows = array($workflowName => $workflow);
        $actualWorkflows = $workflowRegistry->getWorkflowsByEntity($entity);
        $this->assertEquals($expectedWorkflows, $actualWorkflows);
    }
}
