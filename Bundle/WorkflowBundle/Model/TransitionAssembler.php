<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Exception\AssemblerException;
use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowTransitionType;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionFactory;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionFactory;
use Oro\Bundle\WorkflowBundle\Model\Condition\Configurable as ConfigurableCondition;
use Oro\Bundle\WorkflowBundle\Model\Action\Configurable as ConfigurableAction;

class TransitionAssembler extends AbstractAssembler
{
    /**
     * @var ConditionFactory
     */
    protected $conditionFactory;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @param ConditionFactory $conditionFactory
     * @param ActionFactory $actionFactory
     */
    public function __construct(
        ConditionFactory $conditionFactory,
        ActionFactory $actionFactory
    ) {
        $this->conditionFactory = $conditionFactory;
        $this->actionFactory = $actionFactory;
    }

    /**
     * @param array $configuration
     * @param array $definitionsConfiguration
     * @param Step[]|ArrayCollection $steps
     * @return ArrayCollection
     * @throws AssemblerException
     */
    public function assemble(array $configuration, array $definitionsConfiguration, $steps)
    {
        $definitions = $this->parseDefinitions($definitionsConfiguration);

        $transitions = new ArrayCollection();
        foreach ($configuration as $name => $options) {
            $this->assertOptions($options, array('transition_definition'));
            $definitionName = $options['transition_definition'];
            if (!isset($definitions[$definitionName])) {
                throw new AssemblerException(
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
                'pre_conditions' => $this->getOption($options, 'pre_conditions', array()),
                'conditions' => $this->getOption($options, 'conditions', array()),
                'post_actions' => $this->getOption($options, 'post_actions', array()),
                'init_actions' => $this->getOption($options, 'init_actions', array()),
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
     * @throws AssemblerException
     */
    protected function assembleTransition($name, array $options, array $definition, $steps)
    {
        $this->assertOptions($options, array('step_to', 'label'));
        $stepToName = $options['step_to'];
        if (empty($steps[$stepToName])) {
            throw new AssemblerException(sprintf('Step "%s" not found', $stepToName));
        }

        $transition = new Transition();
        $transition->setName($name)
            ->setLabel($options['label'])
            ->setStepTo($steps[$stepToName])
            ->setMessage($this->getOption($options, 'message', null))
            ->setStart($this->getOption($options, 'is_start', false))
            ->setHidden($this->getOption($options, 'is_hidden', false))
            ->setUnavailableHidden($this->getOption($options, 'is_unavailable_hidden', false))
            ->setFormType($this->getOption($options, 'form_type', WorkflowTransitionType::NAME))
            ->setFormOptions($this->getOption($options, 'form_options', array()))
            ->setFrontendOptions($this->getOption($options, 'frontend_options', array()));

        if (!empty($definition['pre_conditions'])) {
            $condition = $this->conditionFactory->create(ConfigurableCondition::ALIAS, $definition['pre_conditions']);
            $transition->setPreCondition($condition);
        }

        if (!empty($definition['conditions'])) {
            $condition = $this->conditionFactory->create(ConfigurableCondition::ALIAS, $definition['conditions']);
            $transition->setCondition($condition);
        }

        if (!empty($definition['post_actions'])) {
            $postAction = $this->actionFactory->create(ConfigurableAction::ALIAS, $definition['post_actions']);
            $transition->setPostAction($postAction);
        }

        if (!empty($definition['init_actions'])) {
            $initAction = $this->actionFactory->create(ConfigurableAction::ALIAS, $definition['init_actions']);
            $transition->setInitAction($initAction);
        }

        return $transition;
    }
}
