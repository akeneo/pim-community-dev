<?php

namespace Oro\Bundle\WorkflowBundle\Configuration;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinitionEntity;
use Oro\Bundle\WorkflowBundle\Configuration\WorkflowConfiguration;
use Oro\Bundle\WorkflowBundle\Exception\MissedRequiredOptionException;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

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
            $this->assertConfigurationOptions($workflowConfiguration, array('label', 'type'));

            $type = $this->getConfigurationOption($workflowConfiguration, 'type', 'entity');
            $enabled = $this->getConfigurationOption($workflowConfiguration, 'enabled', true);
            $startStep = $this->getConfigurationOption($workflowConfiguration, 'start_step', null);

            $managedEntityClasses = $this->getManagedEntityClasses($workflowConfiguration);
            $definitionEntities = $this->buildDefinitionEntities($managedEntityClasses);

            $workflowDefinition = new WorkflowDefinition();
            $workflowDefinition
                ->setName($workflowName)
                ->setLabel($workflowConfiguration['label'])
                ->setType($type)
                ->setEnabled($enabled)
                ->setStartStep($startStep)
                ->setConfiguration($workflowConfiguration)
                ->setWorkflowDefinitionEntities($definitionEntities);

            $workflowDefinitions[] = $workflowDefinition;
        }

        return $workflowDefinitions;
    }

    /**
     * @param array $managedEntityClasses
     * @return WorkflowDefinitionEntity[]
     */
    protected function buildDefinitionEntities(array $managedEntityClasses)
    {
        $definitionEntities = array();

        foreach ($managedEntityClasses as $entityClass) {
            $definitionEntity = new WorkflowDefinitionEntity();
            $definitionEntity->setClassName($entityClass);

            $definitionEntities[] = $definitionEntity;
        }

        return $definitionEntities;
    }

    /**
     * @param array $workflowConfiguration
     * @return array
     */
    protected function getManagedEntityClasses(array $workflowConfiguration)
    {
        $managedEntityClasses = array();

        $attributesData = $this->getConfigurationOption(
            $workflowConfiguration,
            WorkflowConfiguration::NODE_ATTRIBUTES,
            array()
        );

        foreach ($attributesData as $attributeData) {
            $type = $this->getConfigurationOption($attributeData, 'type', null);

            if ($type == 'entity') {
                $options = $this->getConfigurationOption($attributeData, 'options', array());
                $this->assertConfigurationOptions($options, array('class'));

                if (!empty($options[Workflow::MANAGED_ENTITY_KEY])) {
                    $managedEntityClasses[] = $this->getConfigurationOption($options, 'class', null);
                }
            }
        }

        return $managedEntityClasses;
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
