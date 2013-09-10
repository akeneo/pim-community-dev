<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration;

use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\WorkflowBundle\Configuration\WorkflowConfiguration;

class WorkflowConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessConfiguration()
    {
        $workflowConfiguration = new WorkflowConfiguration();

        $inputConfiguration = $this->getInputConfiguration();
        $expectedConfiguration = $this->getExpectedConfiguration();

        foreach ($inputConfiguration as $name => $configuration) {
            $this->assertArrayHasKey($name, $expectedConfiguration);
            $actualConfiguration = $workflowConfiguration->processConfiguration($configuration);
            $this->assertEquals($expectedConfiguration[$name], $actualConfiguration);
        }
    }

    /**
     * @return array
     */
    protected function getInputConfiguration()
    {
        $fileName = __DIR__ . '/Stub/CorrectConfiguration/Resources/config/workflow.yml';
        $this->assertFileExists($fileName);
        $data = Yaml::parse($fileName);

        return current($data);
    }

    /**
     * @return array
     */
    protected function getExpectedConfiguration()
    {
        $fileName = __DIR__ . '/Stub/CorrectConfiguration/Resources/config/workflow.php';
        $this->assertFileExists($fileName);

        return include $fileName;
    }
}
