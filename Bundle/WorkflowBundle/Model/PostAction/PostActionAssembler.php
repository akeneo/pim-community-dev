<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Model\PostAction\ListExecutor;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionFactory;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface;
use Oro\Bundle\WorkflowBundle\Model\AbstractAssembler;
use Oro\Bundle\WorkflowBundle\Model\Pass\PassInterface;

class PostActionAssembler extends AbstractAssembler
{
    const PARAMETERS_KEY = 'parameters';
    const BREAK_ON_FAILURE_KEY = 'break_on_failure';

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
        /** @var ListExecutor $listPostAction */
        $listPostAction = $this->factory->create(ListExecutor::ALIAS);

        foreach ($configuration as $actionConfiguration) {
            if ($this->isService($actionConfiguration)) {
                $options = (array)$this->getEntityParameters($actionConfiguration);
                $actionParameters = $this->getOption($options, self::PARAMETERS_KEY, array());
                $breakOnFailure = $this->getOption($options, self::BREAK_ON_FAILURE_KEY, true);

                $passedActionParameters = $this->configurationPass->pass($actionParameters);
                $actionType = $this->getEntityType($actionConfiguration);
                $serviceName = $this->getServiceName($actionType);

                $postAction = $this->factory->create($serviceName, $passedActionParameters);
                $listPostAction->addPostAction($postAction, $breakOnFailure);
            }
        }

        return $listPostAction;
    }
}
