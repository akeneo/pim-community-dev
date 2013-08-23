<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\WorkflowBundle\Exception\UnknownStepException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationTree;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

class WorkflowAssembler extends AbstractAssembler
{
    /**
     * @var
     */
    protected $container;

    /**
     * @var ConfigurationTree
     */
    protected $configurationTree;

    /**
     * @var AttributeAssembler
     */
    protected $attributeAssembler;

    /**
     * @var StepAssembler
     */
    protected $stepAssembler;

    /**
     * @var TransitionAssembler
     */
    protected $transitionAssembler;

    /**
     * @param ContainerInterface $container
     * @param ConfigurationTree $configurationTree
     * @param AttributeAssembler $attributeAssembler
     * @param StepAssembler $stepAssembler
     * @param TransitionAssembler $transitionAssembler
     */
    public function __construct(
        ContainerInterface $container,
        ConfigurationTree $configurationTree,
        AttributeAssembler $attributeAssembler,
        StepAssembler $stepAssembler,
        TransitionAssembler $transitionAssembler
    ) {
        $this->container = $container;
        $this->configurationTree = $configurationTree;
        $this->attributeAssembler = $attributeAssembler;
        $this->stepAssembler = $stepAssembler;
        $this->transitionAssembler = $transitionAssembler;
    }

    /**
     * @param WorkflowDefinition $workflowDefinition
     * @throws UnknownStepException
     * @throws WorkflowException
     * @return Workflow
     */
    public function assemble(WorkflowDefinition $workflowDefinition)
    {
        $configuration = $this->parseConfiguration($workflowDefinition);
        $this->assertOptions(
            $configuration,
            array(
                ConfigurationTree::NODE_STEPS,
                ConfigurationTree::NODE_TRANSITIONS,
                ConfigurationTree::NODE_TRANSITION_DEFINITIONS
            )
        );

        $attributes = $this->assembleAttributes($configuration);
        $steps = $this->assembleSteps($configuration, $attributes);
        $transitions = $this->assembleTransitions($configuration, $steps);

        $startTransitions = $transitions->filter(
            function (Transition $transition) {
                return $transition->isStart();
            }
        );
        if (!$startTransitions->count()) {
            throw new WorkflowException(
                sprintf(
                    'Workflow "%s" does not contains neither start step nor start transitions',
                    $workflowDefinition->getName()
                )
            );
        }

        $workflow = $this->createWorkflow();
        $workflow
            ->setName($workflowDefinition->getName())
            ->setLabel($workflowDefinition->getLabel())
            ->setEnabled($workflowDefinition->isEnabled())
            ->setAttributes($attributes)
            ->setSteps($steps)
            ->setTransitions($transitions);

        return $workflow;
    }

    protected function parseConfiguration(WorkflowDefinition $workflowDefinition)
    {
        $configuration = $this->configurationTree->parseConfiguration($workflowDefinition->getConfiguration());
        $configuration = $this->prepareDefaultStartTransition($workflowDefinition, $configuration);
        return $configuration;
    }

    protected function prepareDefaultStartTransition(WorkflowDefinition $workflowDefinition, $configuration)
    {
        if ($workflowDefinition->getStartStep()) {
            $startTransitionDefinitionName = Workflow::DEFAULT_START_TRANSITION_NAME . '_definition';
            $configuration[ConfigurationTree::NODE_TRANSITION_DEFINITIONS][$startTransitionDefinitionName] = array();
            $configuration[ConfigurationTree::NODE_TRANSITIONS][Workflow::DEFAULT_START_TRANSITION_NAME] = array(
                'label' => $workflowDefinition->getLabel(),
                'step_to' => $workflowDefinition->getStartStep(),
                'is_start' => true,
                'transition_definition' => $startTransitionDefinitionName
            );
        }
        return $configuration;
    }

    /**
     * @param array $configuration
     * @return Collection
     */
    protected function assembleAttributes(array $configuration)
    {
        $attributesConfiguration = $this->getOption($configuration, ConfigurationTree::NODE_ATTRIBUTES, array());

        return $this->attributeAssembler->assemble($attributesConfiguration);
    }

    /**
     * @param array $configuration
     * @param Collection $attributes
     * @return Collection
     */
    protected function assembleSteps(array $configuration, Collection $attributes)
    {
        $stepsConfiguration = $this->getOption($configuration, ConfigurationTree::NODE_STEPS, array());

        return $this->stepAssembler->assemble($stepsConfiguration, $attributes);
    }

    /**
     * @param array $configuration
     * @param Collection $steps
     * @return Collection
     */
    protected function assembleTransitions(array $configuration, Collection $steps)
    {
        $transitionsConfiguration = $this->getOption($configuration, ConfigurationTree::NODE_TRANSITIONS, array());
        $transitionDefinitionsConfiguration = $this->getOption(
            $configuration,
            ConfigurationTree::NODE_TRANSITION_DEFINITIONS,
            array()
        );

        return $this->transitionAssembler->assemble(
            $transitionsConfiguration,
            $transitionDefinitionsConfiguration,
            $steps
        );
    }

    /**
     * @return Workflow
     */
    protected function createWorkflow()
    {
        return $this->container->get('oro_workflow.workflow_prototype');
    }
}
