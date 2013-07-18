<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration;

use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationProvider;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration\Stub\CorrectConfiguration\CorrectConfigurationBundle;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration\Stub\EmptyConfiguration\EmptyConfigurationBundle;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration\Stub\IncorrectConfiguration\IncorrectConfigurationBundle;

class ConfigurationProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testGetWorkflowDefinitionsIncorrectConfiguration()
    {
        $bundles = array(new IncorrectConfigurationBundle());
        $configurationProvider = new ConfigurationProvider($bundles);
        $configurationProvider->getWorkflowDefinitions();
    }

    public function testGetWorkflowDefinitions()
    {
        $expectedWorkflowConfiguration = $this->getExpectedWokflowConfiguration('CorrectConfiguration');

        $bundles = array(new CorrectConfigurationBundle(), new EmptyConfigurationBundle());
        $configurationProvider = new ConfigurationProvider($bundles);
        $actualWorkflowDefinitions = $configurationProvider->getWorkflowDefinitions();

        $this->assertSameSize($expectedWorkflowConfiguration, $actualWorkflowDefinitions);

        $namedWorkflowDefinitions = array();
        /** @var WorkflowDefinition $workflowDefinition */
        foreach ($actualWorkflowDefinitions as $workflowDefinition) {
            $namedWorkflowDefinitions[$workflowDefinition->getName()] = $workflowDefinition;
        }

        foreach ($expectedWorkflowConfiguration as $workflowName => $workflowConfiguration) {
            // must be in array
            $this->assertArrayHasKey($workflowName, $namedWorkflowDefinitions);
            $workflowDefinition = $namedWorkflowDefinitions[$workflowName];

            // must contain correct configuration
            $this->assertInstanceOf('Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition', $workflowDefinition);
            $this->assertEquals($workflowName, $workflowDefinition->getName());
            $this->assertEquals($workflowConfiguration['label'], $workflowDefinition->getLabel());
            $this->assertEquals($workflowConfiguration['enabled'], $workflowDefinition->isEnabled());
            $this->assertEquals($workflowConfiguration['start_step'], $workflowDefinition->getStartStep());
            $this->assertEquals(
                $workflowConfiguration['managed_entity_class'],
                $workflowDefinition->getManagedEntityClass()
            );
            $this->assertEquals($workflowConfiguration, $workflowDefinition->getConfiguration());
        }
    }

    /**
     * @param string $bundleName
     * @return array
     */
    protected function getExpectedWokflowConfiguration($bundleName)
    {
        $fileName = realpath(__DIR__ . '/Stub/' . $bundleName . '/Resources/config/workflow.yml');
        $data = Yaml::parse(file_get_contents($fileName));
        $this->assertArrayHasKey(ConfigurationProvider::WORKFLOW_ROOT_NODE, $data, 'Invalid stub data');

        return $data[ConfigurationProvider::WORKFLOW_ROOT_NODE];
    }
}
