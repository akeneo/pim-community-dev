<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\Action\CloseWorkflow;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class CloseWorkflowTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Close workflow options are not allowed
     */
    public function testInitializeException()
    {
        $action = new CloseWorkflow();
        $action->initialize(array('anyData'));
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Close is available only for workflow items
     */
    public function testExecuteNotWorfklow()
    {
        $action = new CloseWorkflow();
        $action->execute(new ArrayCollection());
    }

    public function testExecute()
    {
        $workflowItem = new WorkflowItem();
        $this->assertFalse($workflowItem->isClosed());

        $action = new CloseWorkflow();
        $action->execute($workflowItem);
        $this->assertTrue($workflowItem->isClosed());
    }
}
