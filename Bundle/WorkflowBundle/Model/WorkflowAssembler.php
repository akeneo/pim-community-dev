<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationTree;
use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\StepAttribute;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Exception\UnknownTransitionDefinitionException;
use Oro\Bundle\WorkflowBundle\Exception\UnknownStepException;
use Oro\Bundle\WorkflowBundle\Model\Transition;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionFactory;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionFactory;
use Oro\Bundle\WorkflowBundle\Model\Condition\Configurable as ConfigurableCondition;
use Oro\Bundle\WorkflowBundle\Model\PostAction\Configurable as ConfigurablePostAction;

class WorkflowAssembler
{
    /**
     * @var ConfigurationTree
     */
    protected $configurationTree;

    /**
     * @var ConditionFactory
     */
    protected $conditionFactory;

    /**
     * @var PostActionFactory
     */
    protected $postActionFactory;

    /**
     * @param ConfigurationTree $configurationTreeBuilder
     * @param ConditionFactory $conditionFactory
     * @param PostActionFactory $postActionFactory
     */
    public function __construct(
        ConfigurationTree $configurationTreeBuilder,
        ConditionFactory $conditionFactory,
        PostActionFactory $postActionFactory
    ) {
        $this->configurationTree = $configurationTreeBuilder;
        $this->conditionFactory = $conditionFactory;
        $this->postActionFactory = $postActionFactory;
    }

    /**
     * @param WorkflowDefinition $workflowDefinition
     * @return Workflow
     */
    public function assemble(WorkflowDefinition $workflowDefinition)
    {
        $configuration = $this->configurationTree->parseConfiguration($workflowDefinition->getConfiguration());

        $steps = $this->assembleSteps($configuration);
        $transitionDefinitions = $this->parseTransitionDefinitions($configuration);
        $transitions = $this->assembleTransitions($configuration, $transitionDefinitions, $steps);

        $workflow = new Workflow();
        $workflow
            ->setName($workflowDefinition->getName())
            ->setLabel($workflowDefinition->getLabel())
            ->setEnabled($workflowDefinition->isEnabled())
            ->setStartStepName($workflowDefinition->getStartStep())
            ->setManagedEntityClass($workflowDefinition->getManagedEntityClass())
            ->setSteps($steps)
            ->setTransitions($transitions);

        return $workflow;
    }

    /**
     * @param array $configuration
     * @param array $transitionDefinitions
     * @param Step[] $steps
     * @return Transition[]
     * @throws UnknownTransitionDefinitionException
     */
    protected function assembleTransitions(array $configuration, array $transitionDefinitions, $steps)
    {
        $transitionsConfiguration = $configuration[ConfigurationTree::NODE_TRANSITIONS];

        $transitions = array();
        foreach ($transitionsConfiguration as $transitionName => $transitionOptions) {
            $transitionDefinitionName = $transitionOptions['transition_definition'];
            if (!isset($transitionDefinitions[$transitionDefinitionName])) {
                throw new UnknownTransitionDefinitionException(
                    sprintf('Unknown transition definition %s', $transitionDefinitionName)
                );
            }
            $transitionDefinition = $transitionDefinitions[$transitionDefinitionName];

            $transitions[$transitionName] = $this->assembleTransition(
                $transitionName,
                $transitionOptions,
                $transitionDefinition,
                $steps
            );
        }

        return $transitions;
    }

    /**
     * @param string $name
     * @param array $options
     * @param array $definition
     * @param Step[] $steps
     * @return Transition
     * @throws UnknownStepException
     */
    protected function assembleTransition($name, array $options, array $definition, array $steps)
    {
        $stepToName = $options['step_to'];
        if (!isset($steps[$stepToName])) {
            throw new UnknownStepException(sprintf('Unknown step with name %s', $stepToName));
        }
        $stepTo = $steps[$stepToName];

        $transition = new Transition();
        $transition->setName($name);
        $transition->setStepTo($stepTo);

        if (!empty($definition['conditions'])) {
            $condition = $this->conditionFactory->create(ConfigurableCondition::ALIAS, $definition['conditions']);
            $transition->setCondition($condition);
        }

        if (!empty($definition['post_actions'])) {
            $postAction = $this->postActionFactory->create(ConfigurablePostAction::ALIAS, $definition['post_actions']);
            $transition->setPostAction($postAction);
        }

        return $transition;
    }

    /**
     * @param array $configuration
     * @return array
     */
    protected function parseTransitionDefinitions(array $configuration)
    {
        $transitionDefinitionsConfiguration = $configuration[ConfigurationTree::NODE_TRANSITION_DEFINITIONS];

        $transitionDefinitions = array();
        foreach ($transitionDefinitionsConfiguration as $name => $options) {
            $conditions = !empty($options['conditions']) ? $options['conditions'] : array();
            $postActions = !empty($options['post_actions']) ? $options['post_actions'] : array();
            $transitionDefinitions[$name] = array(
                'conditions' => $conditions,
                'post_actions' => $postActions,
            );
        }

        return $transitionDefinitions;
    }

    /**
     * @param array $configuration
     * @return Step[]
     */
    protected function assembleSteps(array $configuration)
    {
        $stepsConfiguration = $configuration[ConfigurationTree::NODE_STEPS];

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

        return $step;
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
