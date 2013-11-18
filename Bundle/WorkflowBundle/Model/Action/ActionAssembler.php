<?php

namespace Oro\Bundle\WorkflowBundle\Model\Action;

use Oro\Bundle\WorkflowBundle\Model\AbstractAssembler;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionFactory;
use Oro\Bundle\WorkflowBundle\Model\Condition\Configurable as ConfigurableCondition;

class ActionAssembler extends AbstractAssembler
{
    const PARAMETERS_KEY = 'parameters';
    const BREAK_ON_FAILURE_KEY = 'break_on_failure';
    const ACTIONS_KEY = 'actions';
    const CONDITIONS_KEY = 'conditions';

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var ConditionFactory
     */
    protected $conditionFactory;

    /**
     * @param ActionFactory $actionFactory
     * @param ConditionFactory $conditionFactory
     */
    public function __construct(
        ActionFactory $actionFactory,
        ConditionFactory $conditionFactory
    ) {
        $this->actionFactory = $actionFactory;
        $this->conditionFactory  = $conditionFactory;
    }

    /**
     * Allowed formats:
     *
     * array(
     *     'conditions' => array(<condition_data>),
     *     'post_actions' => array(
     *         array(<first_post_action_data>),
     *         array(<second_post_action_data>),
     *         ...
     *     )
     * )
     *
     * or
     *
     * array(
     *     array(<first_post_action_data>),
     *     array(<second_post_action_data>),
     *     ...
     * )
     *
     * @param array $configuration
     * @return ActionInterface
     */
    public function assemble(array $configuration)
    {
        /** @var TreeExecutor $treePostAction */
        $treePostAction = $this->actionFactory->create(
            TreeExecutor::ALIAS,
            array(),
            $this->createConfigurableCondition($configuration)
        );

        $actionsConfiguration = $this->getOption($configuration, self::ACTIONS_KEY, $configuration);
        foreach ($actionsConfiguration as $actionConfiguration) {
            if ($this->isService($actionConfiguration)) {
                $options = (array)$this->getEntityParameters($actionConfiguration);
                $breakOnFailure = $this->getOption($options, self::BREAK_ON_FAILURE_KEY, true);

                $actionType = $this->getEntityType($actionConfiguration);
                $serviceName = $this->getServiceName($actionType);

                if ($serviceName == TreeExecutor::ALIAS) {
                    $postAction = $this->assemble($options);
                } else {
                    $actionParameters = $this->getOption($options, self::PARAMETERS_KEY, $options);
                    $passedActionParameters = $this->passConfiguration($actionParameters);
                    $postAction = $this->actionFactory->create(
                        $serviceName,
                        $passedActionParameters,
                        $this->createConfigurableCondition($options)
                    );
                }

                $treePostAction->addAction($postAction, $breakOnFailure);
            }
        }

        return $treePostAction;
    }

    /**
     * @param array $conditionConfiguration
     * @return null|ConfigurableCondition
     */
    protected function createConfigurableCondition(array $conditionConfiguration)
    {
        $condition = null;
        $conditionConfiguration = $this->getOption($conditionConfiguration, self::CONDITIONS_KEY, null);
        if ($conditionConfiguration) {
            $condition = $this->conditionFactory->create(ConfigurableCondition::ALIAS, $conditionConfiguration);
        }

        return $condition;
    }
}
