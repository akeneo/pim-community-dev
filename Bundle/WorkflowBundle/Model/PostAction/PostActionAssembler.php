<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Model\PostAction\ListPostAction;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionFactory;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface;
use Oro\Bundle\WorkflowBundle\Model\AbstractAssembler;
use Oro\Bundle\WorkflowBundle\Model\Pass\PassInterface;

class PostActionAssembler extends AbstractAssembler
{
    const PARAMETERS_KEY = 'parameters';
    const BREAK_ON_FAILURE_KEY = 'breakOnFailure';

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
        /** @var ListPostAction $listPostAction */
        $listPostAction = $this->factory->create(ListPostAction::ALIAS);

        foreach ($configuration as $actionConfiguration) {
            if ($this->isService($actionConfiguration)) {
                $options = (array)$this->getEntityParameters($actionConfiguration);
                $actionParameters = isset($options[self::PARAMETERS_KEY]) ? $options[self::PARAMETERS_KEY] : array();
                $passedActionParameters = $this->configurationPass->pass($actionParameters);

                $actionType = $this->getEntityType($actionConfiguration);
                $postAction = $this->factory->create($actionType, $passedActionParameters);
                $breakOnFailure = !empty($options[self::BREAK_ON_FAILURE_KEY]);
                $listPostAction->addPostAction($postAction, $breakOnFailure);
            }
        }

        return $listPostAction;
    }
}
