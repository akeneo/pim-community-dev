<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Http;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Http\WorkflowItemValidator;

class WorkflowItemValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflowManager;

    /**
     * @var WorkflowItemValidator
     */
    protected $validator;

    protected function setUp()
    {
        $this->workflowManager = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\WorkflowManager')
            ->disableOriginalConstructor()
            ->setMethods(array('isAllManagedEntitiesSpecified'))
            ->getMock();

        $this->validator = new WorkflowItemValidator($this->workflowManager);
    }

    protected function tearDown()
    {
        unset($this->workflowManager);
        unset($this->validator);
    }

    public function testValidate()
    {
        $workflowItem = new WorkflowItem();
        $workflowItem->setWorkflowName('test_workflow');

        $this->workflowManager->expects($this->once())
            ->method('isAllManagedEntitiesSpecified')
            ->with($workflowItem)
            ->will($this->returnValue(true));

        // method must not throw an exception
        $this->validator->validate($workflowItem);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Managed entities for workflow item not found
     */
    public function testValidateManagedEntitiesNotFound()
    {
        $workflowItem = new WorkflowItem();
        $workflowItem->setWorkflowName('test_workflow');

        $this->workflowManager->expects($this->once())
            ->method('isAllManagedEntitiesSpecified')
            ->with($workflowItem)
            ->will($this->returnValue(false));

        $this->validator->validate($workflowItem);
    }
}
