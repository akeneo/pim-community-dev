<?php

namespace Oro\Bundle\WorkflowBundle\Configuration;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationProvider;
use Oro\Bundle\WorkflowBundle\Exception\MissedRequiredOptionException;

class ConfigurationWorkflowDefinitionBuilder
{
    /**
     * @param array $configurationData
     * @return WorkflowDefinition[]
     */
    public function buildFromConfiguration($configurationData)
    {
        $workflowDefinitions = array();
        foreach ($configurationData as $workflowName => $workflowConfiguration) {
            $this->assertConfigurationOptions($workflowConfiguration, array('label', 'start_step'));

            $enabled = $this->getConfigurationOption($workflowConfiguration, 'enabled', true);
            $managedEntityClass = $this->getConfigurationOption($workflowConfiguration, 'managed_entity_class', null);

            $workflowDefinition = new WorkflowDefinition();
            $workflowDefinition
                ->setName($workflowName)
                ->setLabel($workflowConfiguration['label'])
                ->setStartStep($workflowConfiguration['start_step'])
                ->setEnabled($enabled)
                ->setManagedEntityClass($managedEntityClass)
                ->setConfiguration($workflowConfiguration);

            $workflowDefinitions[] = $workflowDefinition;
        }

        return $workflowDefinitions;
    }

    /**
     * @param array $configuration
     * @param array $requiredOptions
     * @throws MissedRequiredOptionException
     */
    protected function assertConfigurationOptions(array $configuration, array $requiredOptions)
    {
        foreach ($requiredOptions as $optionName) {
            if (!isset($configuration[$optionName])) {
                throw new MissedRequiredOptionException(sprintf('Configuration option "%s" is required', $optionName));
            }
        }
    }

    /**
     * @param array $options
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getConfigurationOption(array $options, $key, $default)
    {
        if (isset($options[$key])) {
            return $options[$key];
        }
        return $default;
    }
}
