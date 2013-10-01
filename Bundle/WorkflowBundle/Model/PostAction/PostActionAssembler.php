<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Model\PostAction\TreeExecutor;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionFactory;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface;
use Oro\Bundle\WorkflowBundle\Model\AbstractAssembler;
use Oro\Bundle\WorkflowBundle\Model\Pass\PassInterface;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionFactory;
use Oro\Bundle\WorkflowBundle\Model\Condition\Configurable as ConfigurableCondition;

class PostActionAssembler extends AbstractAssembler
{
    const PARAMETERS_KEY = 'parameters';
    const BREAK_ON_FAILURE_KEY = 'break_on_failure';
    const POST_ACTIONS_KEY = 'post_actions';
    const CONDITIONS_KEY = 'conditions';

    /**
     * @var PostActionFactory
     */
    protected $postActionFactory;

    /**
     * @var ConditionFactory
     */
    protected $conditionFactory;

    /**
     * @var PassInterface
     */
    protected $configurationPass;

    /**
     * @param PostActionFactory $postActionFactory
     * @param ConditionFactory $conditionFactory
     * @param PassInterface $configurationPass
     */
    public function __construct(
        PostActionFactory $postActionFactory,
        ConditionFactory $conditionFactory,
        PassInterface $configurationPass
    ) {
        $this->postActionFactory = $postActionFactory;
        $this->conditionFactory  = $conditionFactory;
        $this->configurationPass = $configurationPass;
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
     * @return PostActionInterface
     */
    public function assemble(array $configuration)
    {
        /** @var TreeExecutor $treePostAction */
        $treePostAction = $this->postActionFactory->create(
            TreeExecutor::ALIAS,
            array(),
            $this->createConfigurableCondition($configuration)
        );

        $actionsConfiguration = $this->getOption($configuration, self::POST_ACTIONS_KEY, $configuration);
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
                    $passedActionParameters = $this->configurationPass->pass($actionParameters);
                    $postAction = $this->postActionFactory->create(
                        $serviceName,
                        $passedActionParameters,
                        $this->createConfigurableCondition($options)
                    );
                }

                $treePostAction->addPostAction($postAction, $breakOnFailure);
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
