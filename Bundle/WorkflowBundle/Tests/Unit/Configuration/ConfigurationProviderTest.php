<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration;

use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationProvider;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration\Stub\CorrectConfiguration\CorrectConfigurationBundle;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration\Stub\EmptyConfiguration\EmptyConfigurationBundle;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration\Stub\IncorrectConfiguration\IncorrectConfigurationBundle;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration\Stub\DuplicateConfiguration\DuplicateConfigurationBundle;
use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationTree;

class ConfigurationProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testGetWorkflowDefinitionsIncorrectConfiguration()
    {
        $bundles = array(new IncorrectConfigurationBundle());
        $configurationProvider = new ConfigurationProvider($bundles, new ConfigurationTree());
        $configurationProvider->getWorkflowDefinitionConfiguration();
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testGetWorkflowDefinitionsDuplicateConfiguration()
    {
        $bundles = array(new CorrectConfigurationBundle(), new DuplicateConfigurationBundle());
        $configurationProvider = new ConfigurationProvider($bundles, new ConfigurationTree());
        $configurationProvider->getWorkflowDefinitionConfiguration();
    }

    public function testGetWorkflowDefinitions()
    {
        $expectedWorkflowConfiguration = $this->getExpectedWokflowConfiguration('CorrectConfiguration');

        $bundles = array(new CorrectConfigurationBundle(), new EmptyConfigurationBundle());
        $configurationProvider = new ConfigurationProvider($bundles, new ConfigurationTree());
        $actualWorkflowDefinitions = $configurationProvider->getWorkflowDefinitionConfiguration();

        $this->assertSameSize($expectedWorkflowConfiguration, $actualWorkflowDefinitions);

        foreach ($expectedWorkflowConfiguration as $workflowName => $workflowConfiguration) {
            $this->assertArrayHasKey($workflowName, $actualWorkflowDefinitions);
            $actualWorkflowDefinition = $actualWorkflowDefinitions[$workflowName];

            $this->assertEquals($workflowConfiguration, $actualWorkflowDefinition);
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
        $this->assertArrayHasKey(ConfigurationProvider::NODE_WORKFLOWS, $data, 'Invalid stub data');

        return $data[ConfigurationProvider::NODE_WORKFLOWS];
    }
}
