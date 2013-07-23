<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationTreeBuilder;
use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\StepAttribute;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

class WorkflowAssembler
{
    protected $configurationTreeBuilder;

    public function __construct(
        ConfigurationTreeBuilder $configurationTreeBuilder
    ) {
        $this->configurationTreeBuilder = $configurationTreeBuilder;
    }

    public function assemble(WorkflowDefinition $workflowDefinition)
    {
        $configuration = $this->getConfiguration($workflowDefinition);
        $steps = $this->assembleSteps($configuration);

        $workflow = new Workflow();
        $workflow
            ->setName($workflowDefinition->getName())
            ->setLabel($workflowDefinition->getLabel())
            ->setEnabled($workflowDefinition->isEnabled())
            ->setStartStepName($workflowDefinition->getStartStep())
            ->setManagedEntityClass($workflowDefinition->getManagedEntityClass())
            ->setSteps($steps);

        return $workflow;
    }

    /**
     * @param WorkflowDefinition $workflowDefinition
     * @return array
     */
    protected function getConfiguration(WorkflowDefinition $workflowDefinition)
    {
        $sourceConfiguration = $workflowDefinition->getConfiguration();
        $configurationTreeNode = $this->configurationTreeBuilder->buildTree();

        return $configurationTreeNode->finalize($sourceConfiguration);
    }

    /**
     * @param array $configuration
     * @return Step[]
     */
    protected function assembleSteps(array $configuration)
    {
        $stepsConfiguration = $configuration[ConfigurationTreeBuilder::NODE_STEPS];

        $steps = array();
        foreach ($stepsConfiguration as $stepName => $stepOptions) {
            $steps[$stepName] = $this->assembleStep($stepName, $stepOptions);
        }

        return $steps;
    }

    /**
     * @param string $name
     * @param array $options
     * @return Step
     */
    protected function assembleStep($name, array $options)
    {
        $allowedTransitions = !empty($options['allowed_transitions']) ? $options['allowed_transitions'] : array();
        $attributes = !empty($options['attributes']) ? $this->assembleStepAttributes($options['attributes']) : array();

        $step = new Step();
        $step->setName($name);
        $step->setLabel($options['label']);
        $step->setTemplate($options['template']);
        $step->setOrder($options['order']);
        $step->setIsFinal($options['is_final']);
        $step->setAllowedTransitions($allowedTransitions);
        $step->setAttributes($attributes);

        return null;
    }

    /**
     * @param array $configuration
     * @return StepAttribute[]
     */
    protected function assembleStepAttributes(array $configuration)
    {
        $attributes = array();
        foreach ($configuration as $attributeName => $attributeOptions) {
            $attributes[$attributeName] = $this->assembleStepAttribute($attributeName, $attributeOptions);
        }

        return $attributes;
    }

    /**
     * @param string $name
     * @param array $options
     * @return StepAttribute
     */
    protected function assembleStepAttribute($name, array $options)
    {
        $attributeOptions = !empty($options['options']) ? $options['options'] : array();

        $stepAttribute = new StepAttribute();
        $stepAttribute->setName($name);
        $stepAttribute->setLabel($options['label']);
        $stepAttribute->setFormTypeName($options['form_type']);
        $stepAttribute->setOptions($attributeOptions);

        return $stepAttribute;
    }
}
