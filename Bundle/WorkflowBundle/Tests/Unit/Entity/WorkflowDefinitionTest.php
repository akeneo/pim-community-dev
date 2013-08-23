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

    public function testStartStep()
    {
        $this->assertNull($this->workflowDefinition->getStartStep());
        $value = 'step_one';
        $this->workflowDefinition->setStartStep($value);
        $this->assertEquals($value, $this->workflowDefinition->getStartStep());
    }

    public function testConfiguration()
    {
        $this->assertEmpty($this->workflowDefinition->getConfiguration());
        $value = array('some', 'configuration', 'array');
        $this->workflowDefinition->setConfiguration($value);
        $this->assertEquals($value, $this->workflowDefinition->getConfiguration());
    }

    public function testImport()
    {
        $expectedData = array(
            'name' => 'test_name',
            'label' => 'test_label',
            'enabled' => false,
            'start_step' => 'test_step',
            'configuration' => array('test', 'configuration')
        );

        $this->assertNotEquals($expectedData, $this->getDataAsArray($this->workflowDefinition));

        $newDefinition = new WorkflowDefinition();
        $newDefinition->setName($expectedData['name'])
            ->setLabel($expectedData['label'])
            ->setEnabled($expectedData['enabled'])
            ->setStartStep($expectedData['start_step'])
            ->setConfiguration($expectedData['configuration']);

        $this->workflowDefinition->import($newDefinition);
        $this->assertEquals($expectedData, $this->getDataAsArray($this->workflowDefinition));
    }

    /**
     * @param WorkflowDefinition $definition
     * @return array
     */
    protected function getDataAsArray(WorkflowDefinition $definition)
    {
        return array(
            'name' => $definition->getName(),
            'label' => $definition->getLabel(),
            'enabled' => $definition->isEnabled(),
            'start_step' => $definition->getStartStep(),
            'configuration' => $definition->getConfiguration()
        );
    }
}
