<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\Transition;
use Oro\Bundle\WorkflowBundle\Exception\UnknownTransitionDefinitionException;
use Oro\Bundle\WorkflowBundle\Exception\UnknownStepException;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionFactory;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionFactory;
use Oro\Bundle\WorkflowBundle\Model\Condition\Configurable as ConfigurableCondition;
use Oro\Bundle\WorkflowBundle\Model\PostAction\Configurable as ConfigurablePostAction;

class TransitionAssembler extends AbstractAssembler
{
    /**
     * @var ConditionFactory
     */
    protected $conditionFactory;

    /**
     * @var PostActionFactory
     */
    protected $postActionFactory;

    /**
     * @param ConditionFactory $conditionFactory
     * @param PostActionFactory $postActionFactory
     */
    public function __construct(
        ConditionFactory $conditionFactory,
        PostActionFactory $postActionFactory
    ) {
        $this->conditionFactory = $conditionFactory;
        $this->postActionFactory = $postActionFactory;
    }

    /**
     * @param array $configuration
     * @param array $definitionsConfiguration
     * @param Step[]|ArrayCollection $steps
     * @return ArrayCollection
     * @throws UnknownTransitionDefinitionException
     */
    public function assemble(array $configuration, array $definitionsConfiguration, $steps)
    {
        $definitions = $this->parseDefinitions($definitionsConfiguration);

        $transitions = new ArrayCollection();
        foreach ($configuration as $name => $options) {
            $this->assertOptions($options, array('transition_definition'));
            $definitionName = $options['transition_definition'];
            if (!isset($definitions[$definitionName])) {
                throw new UnknownTransitionDefinitionException(
                    sprintf('Unknown transition definition %s', $definitionName)
                );
            }
            $definition = $definitions[$definitionName];

            $transition = $this->assembleTransition($name, $options, $definition, $steps);
            $transitions->set($name, $transition);
        }

        return $transitions;
    }

    /**
     * @param array $configuration
     * @return array
     */
    protected function parseDefinitions(array $configuration)
    {
        $definitions = array();
        foreach ($configuration as $name => $options) {
            $definitions[$name] = array(
                'conditions' => $this->getOption($options, 'conditions', array()),
                'post_actions' => $this->getOption($options, 'post_actions', array()),
            );
        }

        return $definitions;
    }

    /**
     * @param string $name
     * @param array $options
     * @param array $definition
     * @param Step[]|ArrayCollection $steps
     * @return Transition
     * @throws UnknownStepException
     */
    protected function assembleTransition($name, array $options, array $definition, $steps)
    {
        $this->assertOptions($options, array('step_to', 'label'));
        $stepToName = $options['step_to'];
        if (empty($steps[$stepToName])) {
            throw new UnknownStepException($stepToName);
        }
        $stepTo = $steps[$stepToName];

        $transition = new Transition();
        $transition->setName($name);
        $transition->setStepTo($stepTo);
        $transition->setLabel($options['label']);

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
}
