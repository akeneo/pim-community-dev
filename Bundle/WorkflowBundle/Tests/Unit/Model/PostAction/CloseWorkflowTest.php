<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\PostAction\CloseWorkflow;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class CloseWorkflowTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Close workflow options are not allowed
     */
    public function testInitializeException()
    {
        $postAction = new CloseWorkflow();
        $postAction->initialize(array('anyData'));
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Close is available only for workflow items
     */
    public function testExecuteNotWorfklow()
    {
        $postAction = new CloseWorkflow();
        $postAction->execute(new ArrayCollection());
    }

    public function testExecute()
    {
        $workflowItem = new WorkflowItem();
        $this->assertFalse($workflowItem->isClosed());

        $postAction = new CloseWorkflow();
        $postAction->execute($workflowItem);
        $this->assertTrue($workflowItem->isClosed());
    }
}
