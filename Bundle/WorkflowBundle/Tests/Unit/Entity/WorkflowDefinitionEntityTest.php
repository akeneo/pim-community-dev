<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Entity;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinitionEntity;

class WorkflowDefinitionEntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WorkflowDefinitionEntity
     */
    protected $workflowDefinitionEntity;

    protected function setUp()
    {
        $this->workflowDefinitionEntity = new WorkflowDefinitionEntity();
    }

    protected function tearDown()
    {
        unset($this->workflowDefinitionEntity);
    }

    public function testGetId()
    {
        $this->assertNull($this->workflowDefinitionEntity->getId());

        $value = 42;
        $reflectionProperty = new \ReflectionProperty(
            'Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinitionEntity',
            'id'
        );
        $reflectionProperty->setAccessible(true);

        $reflectionProperty->setValue($this->workflowDefinitionEntity, $value);
        $this->assertEquals($value, $this->workflowDefinitionEntity->getId());
    }

    public function testGetSetClassName()
    {
        $this->assertNull($this->workflowDefinitionEntity->getClassName());
        $value = 'TestClass';
        $this->workflowDefinitionEntity->setClassName($value);
        $this->assertEquals($value, $this->workflowDefinitionEntity->getClassName());
    }

    public function testGetSetWorkflowDefinition()
    {
        $this->assertNull($this->workflowDefinitionEntity->getWorkflowDefinition());

        $value = new WorkflowDefinition();
        $value->setName('test_workflow');

        $this->workflowDefinitionEntity->setWorkflowDefinition($value);
        $this->assertEquals($value, $this->workflowDefinitionEntity->getWorkflowDefinition());
    }
}
