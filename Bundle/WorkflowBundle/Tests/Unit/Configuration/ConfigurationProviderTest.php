<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration;

use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationProvider;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration\Stub\CorrectConfiguration\CorrectConfigurationBundle;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration\Stub\EmptyConfiguration\EmptyConfigurationBundle;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration\Stub\IncorrectConfiguration\IncorrectConfigurationBundle;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration\Stub\DuplicateConfiguration\DuplicateConfigurationBundle;
use Oro\Bundle\WorkflowBundle\Configuration\WorkflowListConfiguration;

class ConfigurationProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testGetWorkflowDefinitionsIncorrectConfiguration()
    {
        $bundles = array(new IncorrectConfigurationBundle());
        $configurationProvider = new ConfigurationProvider($bundles, new WorkflowListConfiguration());
        $configurationProvider->getWorkflowDefinitionConfiguration();
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testGetWorkflowDefinitionsDuplicateConfiguration()
    {
        $bundles = array(new CorrectConfigurationBundle(), new DuplicateConfigurationBundle());
        $configurationProvider = new ConfigurationProvider($bundles, new WorkflowListConfiguration());
        $configurationProvider->getWorkflowDefinitionConfiguration();
    }

    public function testGetWorkflowDefinitions()
    {
        $bundles = array(new CorrectConfigurationBundle(), new EmptyConfigurationBundle());
        $configurationProvider = new ConfigurationProvider($bundles, new WorkflowListConfiguration());

        $this->assertEquals(
            $this->getExpectedWokflowConfiguration('CorrectConfiguration'),
            $configurationProvider->getWorkflowDefinitionConfiguration()
        );
    }

    /**
     * @param string $bundleName
     * @return array
     */
    protected function getExpectedWokflowConfiguration($bundleName)
    {
        $fileName = __DIR__ . '/Stub/' . $bundleName . '/Resources/config/workflow.php';
        $this->assertFileExists($fileName);
        return include $fileName;
    }
}
