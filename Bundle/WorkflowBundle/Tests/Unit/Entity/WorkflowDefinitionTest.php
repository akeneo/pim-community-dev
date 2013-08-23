<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Entity;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinitionEntity;

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

    protected function tearDown()
    {
        unset($this->workflowDefinition);
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
            'configuration' => array('test', 'configuration'),
            'entities' => array(
                array('class' => 'TestClass')
            )
        );

        $this->assertNotEquals($expectedData, $this->getDefinitionAsArray($this->workflowDefinition));

        $definitionEntity = new WorkflowDefinitionEntity();
        $definitionEntity->setClassName($expectedData['entities'][0]['class']);

        $newDefinition = new WorkflowDefinition();
        $newDefinition->setName($expectedData['name'])
            ->setLabel($expectedData['label'])
            ->setEnabled($expectedData['enabled'])
            ->setStartStep($expectedData['start_step'])
            ->setConfiguration($expectedData['configuration'])
            ->setWorkflowDefinitionEntities(array($definitionEntity));

        $this->workflowDefinition->import($newDefinition);
        $this->assertEquals($expectedData, $this->getDefinitionAsArray($this->workflowDefinition));
    }

    /**
     * @param WorkflowDefinition $definition
     * @return array
     */
    protected function getDefinitionAsArray(WorkflowDefinition $definition)
    {
        $entitiesData = array();
        /** @var WorkflowDefinitionEntity $entity */
        foreach ($definition->getWorkflowDefinitionEntities() as $entity) {
            $entitiesData[] = array('class' => $entity->getClassName());
        }

        return array(
            'name' => $definition->getName(),
            'label' => $definition->getLabel(),
            'enabled' => $definition->isEnabled(),
            'start_step' => $definition->getStartStep(),
            'configuration' => $definition->getConfiguration(),
            'entities' => $entitiesData,
        );
    }

    public function testSetWorkflowDefinitionEntities()
    {
        $firstEntity = new WorkflowDefinitionEntity();
        $firstEntity->setClassName('FirstClass');

        $secondEntity = new WorkflowDefinitionEntity();
        $secondEntity->setClassName('SecondClass');

        $secondEntitySameClass = new WorkflowDefinitionEntity();
        $secondEntitySameClass->setClassName('SecondClass');

        $thirdEntity = new WorkflowDefinitionEntity();
        $thirdEntity->setClassName('ThirdClass');

        $newDefinition = new WorkflowDefinition();
        $newDefinition->setWorkflowDefinitionEntities(array($firstEntity, $secondEntity));

        $this->assertEquals(
            array($firstEntity, $secondEntity),
            array_values($newDefinition->getWorkflowDefinitionEntities()->toArray())
        );

        $newDefinition->setWorkflowDefinitionEntities(array($secondEntitySameClass, $thirdEntity));

        $this->assertEquals(
            array($secondEntity, $thirdEntity),
            array_values($newDefinition->getWorkflowDefinitionEntities()->toArray())
        );
    }
}
