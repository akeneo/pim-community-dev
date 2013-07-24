<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\Collection;
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
     * @param ConfigurationTree $configurationTreeBuilder
     * @param AttributeAssembler $attributeAssembler
     * @param StepAssembler $stepAssembler
     * @param TransitionAssembler $transitionAssembler
     */
    public function __construct(
        ContainerInterface $container,
        ConfigurationTree $configurationTreeBuilder,
        AttributeAssembler $attributeAssembler,
        StepAssembler $stepAssembler,
        TransitionAssembler $transitionAssembler
    ) {
        $this->container = $container;
        $this->configurationTree = $configurationTreeBuilder;
        $this->attributeAssembler = $attributeAssembler;
        $this->stepAssembler = $stepAssembler;
        $this->transitionAssembler = $transitionAssembler;
    }

    /**
     * @param WorkflowDefinition $workflowDefinition
     * @return Workflow
     */
    public function assemble(WorkflowDefinition $workflowDefinition)
    {
        $configuration = $this->configurationTree->parseConfiguration($workflowDefinition->getConfiguration());
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

        $workflow = $this->createWorkflow();
        $workflow
            ->setName($workflowDefinition->getName())
            ->setLabel($workflowDefinition->getLabel())
            ->setEnabled($workflowDefinition->isEnabled())
            ->setStartStepName($workflowDefinition->getStartStep())
            ->setManagedEntityClass($workflowDefinition->getManagedEntityClass())
            ->setAttributes($attributes)
            ->setSteps($steps)
            ->setTransitions($transitions);

        return $workflow;
    }

    /**
     * @param array $configuration
     * @return Collection
     */
    protected function assembleAttributes(array $configuration)
    {
        $attributesConfiguration = !empty($configuration[ConfigurationTree::NODE_ATTRIBUTES])
            ? $configuration[ConfigurationTree::NODE_ATTRIBUTES]
            : array();

        return $this->attributeAssembler->assemble($attributesConfiguration);
    }

    /**
     * @param array $configuration
     * @param Collection $attributes
     * @return Collection
     */
    protected function assembleSteps(array $configuration, Collection $attributes)
    {
        $stepsConfiguration = $configuration[ConfigurationTree::NODE_STEPS];

        return $this->stepAssembler->assemble($stepsConfiguration, $attributes);
    }

    /**
     * @param array $configuration
     * @param Collection $steps
     * @return Collection
     */
    protected function assembleTransitions(array $configuration, Collection $steps)
    {
        $transitionsConfiguration = $configuration[ConfigurationTree::NODE_TRANSITIONS];
        $transitionDefinitionsConfiguration = $configuration[ConfigurationTree::NODE_TRANSITION_DEFINITIONS];

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
