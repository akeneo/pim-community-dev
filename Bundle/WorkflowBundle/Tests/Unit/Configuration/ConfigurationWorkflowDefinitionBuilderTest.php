<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationWorkflowDefinitionBuilder;
use Oro\Bundle\WorkflowBundle\Configuration\WorkflowConfiguration;

class ConfigurationWorkflowDefinitionBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param WorkflowDefinition $definition
     * @return array
     */
    protected function getDataAsArray(WorkflowDefinition $definition)
    {
        $entitiesData = array();
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

    /**
     * @param array $expectedData
     * @param array $inputData
     * @dataProvider buildFromConfigurationDataProvider
     */
    public function testBuildFromConfiguration(array $expectedData, array $inputData)
    {
        $builder = new ConfigurationWorkflowDefinitionBuilder();
        $workflowDefinitions = $builder->buildFromConfiguration($inputData);
        $this->assertCount(1, $workflowDefinitions);

        $workflowDefinition = current($workflowDefinitions);
        $this->assertEquals($expectedData, $this->getDataAsArray($workflowDefinition));
    }

    /**
     * @return array
     */
    public function buildFromConfigurationDataProvider()
    {
        $minimumConfiguration = array(
            'label'      => 'Test Workflow',
            'start_step' => 'test_step',
            'type'       => 'entity'
        );

        $maximumConfiguration = array(
            'label' => 'Test Workflow',
            'enabled' => false,
            'start_step' => 'test_step',
            'type' => 'entity',
            WorkflowConfiguration::NODE_ATTRIBUTES => array(
                array(
                    'name' => 'string_attribute',
                    'type' => 'string',
                ),
                array(
                    'name' => 'entity_attribute',
                    'type' => 'entity',
                    'options' => array(
                        'class' => 'TestClass',
                    ),
                ),
                array(
                    'name' => 'managed_entity_attribute',
                    'type' => 'entity',
                    'options' => array(
                        'class' => 'TestManagedClass',
                        'managed_entity' => true,
                    ),
                ),
            )
        );

        return array(
            'minimum configuration' => array(
                'expectedData' => array(
                    'name'  => 'test_workflow',
                    'label' => 'Test Workflow',
                    'enabled' => true,
                    'start_step' => 'test_step',
                    'configuration' => $minimumConfiguration,
                    'entities' => array(),

                ),
                'inputData' => array(
                    'test_workflow' => $minimumConfiguration,
                ),
            ),
            'maximum configuration' => array(
                'expectedData' => array(
                    'name'  => 'test_workflow',
                    'label' => 'Test Workflow',
                    'enabled' => false,
                    'start_step' => 'test_step',
                    'configuration' => $maximumConfiguration,
                    'entities' => array(
                        array('class' => 'TestManagedClass')
                    )
                ),
                'inputData' => array(
                    'test_workflow' => $maximumConfiguration,
                ),
            ),
        );
    }

    /**
     * @param array $expectedData
     * @param array $inputData
     * @dataProvider buildFromConfigurationDataProvider
     */

    /**
     * @param string $expectedException
     * @param string $expectedMessage
     * @param array $inputData
     * @dataProvider buildFromConfigurationExceptionDataProvider
     */
    public function testBuildFromConfigurationException($expectedException, $expectedMessage, array $inputData)
    {
        $this->setExpectedException($expectedException, $expectedMessage);

        $builder = new ConfigurationWorkflowDefinitionBuilder();
        $builder->buildFromConfiguration($inputData);
    }

    /**
     * @return array
     */
    public function buildFromConfigurationExceptionDataProvider()
    {
        return array(
            'no label' => array(
                'expectedException' => '\Oro\Bundle\WorkflowBundle\Exception\MissedRequiredOptionException',
                'expectedMessage' => 'Configuration option "label" is required',
                'inputData' => array(
                    'test_workflow' => array(),
                ),
            ),
        );
    }
}
