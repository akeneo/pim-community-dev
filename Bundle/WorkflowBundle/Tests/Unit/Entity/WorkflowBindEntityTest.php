<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Entity;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowBindEntity;

class WorkflowBindEntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WorkflowBindEntity
     */
    protected $workflowBindEntity;

    protected function setUp()
    {
        $this->workflowBindEntity = new WorkflowBindEntity();
    }

    public function testId()
    {
        $this->assertNull($this->workflowBindEntity->getId());
        $value = 1;
        $this->workflowBindEntity->setId($value);
        $this->assertEquals($value, $this->workflowBindEntity->getId());
    }

    public function testEntityClass()
    {
        $this->assertNull($this->workflowBindEntity->getEntityClass());
        $value = 'Foo';
        $this->workflowBindEntity->setEntityClass($value);
        $this->assertEquals($value, $this->workflowBindEntity->getEntityClass());
    }

    public function testEntityId()
    {
        $this->assertNull($this->workflowBindEntity->getEntityId());
        $value = 1;
        $this->workflowBindEntity->setEntityId($value);
        $this->assertEquals($value, $this->workflowBindEntity->getEntityId());
    }

    public function testWorkflowItem()
    {
        $this->assertNull($this->workflowBindEntity->getWorkflowItem());
        $value = new WorkflowItem();
        $this->workflowBindEntity->setWorkflowItem($value);
        $this->assertEquals($value, $this->workflowBindEntity->getWorkflowItem());
    }

    /**
     * @dataProvider hasSameEntityDataProvider
     */
    public function testHasSameEntity(WorkflowBindEntity $first, WorkflowBindEntity $second, $expected)
    {
        $this->assertEquals($expected, $first->hasSameEntity($second));
    }

    public function hasSameEntityDataProvider()
    {
        return array(
            array(
                $this->createWorkflowBindEntity(null, null),
                $this->createWorkflowBindEntity(null, null),
                false
            ),
            array(
                $this->createWorkflowBindEntity(1, null),
                $this->createWorkflowBindEntity(1, null),
                false
            ),
            array(
                $this->createWorkflowBindEntity(null, 'SomeClass'),
                $this->createWorkflowBindEntity(null, 'SomeClass'),
                false
            ),
            array(
                $this->createWorkflowBindEntity(1, 'SomeClass'),
                $this->createWorkflowBindEntity(2, 'SomeClass'),
                false
            ),
            array(
                $this->createWorkflowBindEntity(1, 'SomeClassA'),
                $this->createWorkflowBindEntity(1, 'SomeClassB'),
                false
            ),
            array(
                $this->createWorkflowBindEntity(1, 'SomeClass'),
                $this->createWorkflowBindEntity(1, 'SomeClass'),
                true
            ),
        );
    }

    protected function createWorkflowBindEntity($entityId, $entityClass)
    {
        $result = new WorkflowBindEntity();
        $result->setEntityId($entityId);
        $result->setEntityClass($entityClass);

        return $result;
    }
}
