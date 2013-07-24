<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationTree;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

class WorkflowAssembler
{
    /**
     * @var ConfigurationTree
     */
    protected $configurationTree;

    /**
     * @var StepAssembler
     */
    protected $stepAssembler;

    /**
     * @var TransitionAssembler
     */
    protected $transitionAssembler;

    /**
     * @param ConfigurationTree $configurationTreeBuilder
     * @param StepAssembler $stepAssembler
     * @param TransitionAssembler $transitionAssembler
     */
    public function __construct(
        ConfigurationTree $configurationTreeBuilder,
        StepAssembler $stepAssembler,
        TransitionAssembler $transitionAssembler
    ) {
        $this->configurationTree = $configurationTreeBuilder;
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

        $steps = $this->assembleSteps($configuration);
        $transitions = $this->assembleTransitions($configuration, $steps);

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
     * @return ArrayCollection
     */
    protected function assembleSteps(array $configuration)
    {
        $stepsConfiguration = $configuration[ConfigurationTree::NODE_STEPS];

        return $this->stepAssembler->assemble($stepsConfiguration);
    }

    /**
     * @param array $configuration
     * @param ArrayCollection $steps
     * @return ArrayCollection
     */
    protected function assembleTransitions(array $configuration, ArrayCollection $steps)
    {
        $transitionsConfiguration = $configuration[ConfigurationTree::NODE_TRANSITIONS];
        $transitionDefinitionsConfiguration = $configuration[ConfigurationTree::NODE_TRANSITION_DEFINITIONS];

        return $this->transitionAssembler->assemble(
            $transitionsConfiguration,
            $transitionDefinitionsConfiguration,
            $steps
        );
    }
}
