<?php

namespace Oro\Bundle\WorkflowBundle\Entity;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItemEntity;

class WorkflowItemEntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WorkflowItemEntity
     */
    protected $workflowItemEntity;

    protected function setUp()
    {
        $this->workflowItemEntity = new WorkflowItemEntity();
    }

    public function testId()
    {
        $this->assertNull($this->workflowItemEntity->getId());
        $value = 1;
        $this->workflowItemEntity->setId($value);
        $this->assertEquals($value, $this->workflowItemEntity->getId());
    }

    public function testStepName()
    {
        $this->assertNull($this->workflowItemEntity->getStepName());
        $value = 'example_step';
        $this->workflowItemEntity->setStepName($value);
        $this->assertEquals($value, $this->workflowItemEntity->getStepName());
    }

    public function testEntityClass()
    {
        $this->assertNull($this->workflowItemEntity->getEntityClass());
        $value = 'Foo';
        $this->workflowItemEntity->setEntityClass($value);
        $this->assertEquals($value, $this->workflowItemEntity->getEntityClass());
    }

    public function testEntityId()
    {
        $this->assertNull($this->workflowItemEntity->getEntityId());
        $value = 1;
        $this->workflowItemEntity->setEntityId($value);
        $this->assertEquals($value, $this->workflowItemEntity->getEntityId());
    }

    public function testWorkflowItem()
    {
        $this->assertNull($this->workflowItemEntity->getWorkflowItem());
        $value = new WorkflowItem();
        $this->workflowItemEntity->setWorkflowItem($value);
        $this->assertEquals($value, $this->workflowItemEntity->getWorkflowItem());
    }
}
