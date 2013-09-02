<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Model\PostAction\TreeExecutor;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionFactory;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface;
use Oro\Bundle\WorkflowBundle\Model\AbstractAssembler;
use Oro\Bundle\WorkflowBundle\Model\Pass\PassInterface;

class PostActionAssembler extends AbstractAssembler
{
    const PARAMETERS_KEY = 'parameters';
    const BREAK_ON_FAILURE_KEY = 'break_on_failure';
    const POST_ACTIONS_KEY = 'post_actions';

    /**
     * @var PostActionFactory
     */
    protected $factory;

    /**
     * @var PassInterface
     */
    protected $configurationPass;

    /**
     * @param PostActionFactory $factory
     * @param PassInterface $configurationPass
     */
    public function __construct(PostActionFactory $factory, PassInterface $configurationPass)
    {
        $this->factory           = $factory;
        $this->configurationPass = $configurationPass;
    }

    /**
     * @param array $configuration
     * @return PostActionInterface
     */
    public function assemble(array $configuration)
    {
        /** @var TreeExecutor $listPostAction */
        $listPostAction = $this->factory->create(TreeExecutor::ALIAS);

        foreach ($configuration as $actionConfiguration) {
            if ($this->isService($actionConfiguration)) {
                $options = (array)$this->getEntityParameters($actionConfiguration);
                $breakOnFailure = $this->getOption($options, self::BREAK_ON_FAILURE_KEY, true);

                $actionType = $this->getEntityType($actionConfiguration);
                $serviceName = $this->getServiceName($actionType);

                if ($serviceName == TreeExecutor::ALIAS) {
                    $treeConfiguration = $this->getOption($options, self::POST_ACTIONS_KEY, $options);
                    $postAction = $this->assemble($treeConfiguration);
                } else {
                    $actionParameters = $this->getOption($options, self::PARAMETERS_KEY, $options);
                    $passedActionParameters = $this->configurationPass->pass($actionParameters);
                    $postAction = $this->factory->create($serviceName, $passedActionParameters);
                }

                $listPostAction->addPostAction($postAction, $breakOnFailure);
            }
        }

        return $listPostAction;
    }
}
