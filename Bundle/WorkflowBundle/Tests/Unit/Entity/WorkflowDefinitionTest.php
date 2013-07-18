<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Entity;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;

class WorkflowDefinitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WorkflowDefinition
     */
    protected $workflowDefinition;

    protected function setUp()
    {
        $this->workflowDefinition = new WorkflowDefinition();
    }

    public function testName()
    {
        $this->assertNull($this->workflowDefinition->getName());
        $value = 'example_workflow';
        $this->workflowDefinition->setName($value);
        $this->assertEquals($value, $this->workflowDefinition->getName());
    }

    public function testLabel()
    {
        $this->assertNull($this->workflowDefinition->getLabel());
        $value = 'Example Workflow';
        $this->workflowDefinition->setLabel($value);
        $this->assertEquals($value, $this->workflowDefinition->getLabel());
    }

    public function testEnabled()
    {
        $this->assertFalse($this->workflowDefinition->isEnabled());
        $this->workflowDefinition->setEnabled(true);
        $this->assertTrue($this->workflowDefinition->isEnabled());
    }

    public function testManagedEntityClass()
    {
        $this->assertNull($this->workflowDefinition->getManagedEntityClass());
        $value = 'stdClass';
        $this->workflowDefinition->setManagedEntityClass($value);
        $this->assertEquals($value, $this->workflowDefinition->getManagedEntityClass());
    }

    public function testConfiguration()
    {
        $this->assertNull($this->workflowDefinition->getConfiguration());
        $value = 'some_configuration_string';
        $this->workflowDefinition->setConfiguration($value);
        $this->assertEquals($value, $this->workflowDefinition->getConfiguration());
    }
}
