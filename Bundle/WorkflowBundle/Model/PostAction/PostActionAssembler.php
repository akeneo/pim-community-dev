<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\WorkflowBundle\Model\PostAction\ListPostAction;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionFactory;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface;
use Oro\Bundle\WorkflowBundle\Model\AbstractAssembler;
use Oro\Bundle\WorkflowBundle\Model\Pass\PassInterface;

class PostActionAssembler extends AbstractAssembler
{
    /**
     * @var PostActionFactory
     */
    protected $factory;

    /**
     * @var PassInterface
     */
    protected $configurationPass;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @param PostActionFactory $factory
     * @param PassInterface $configurationPass
     */
    public function __construct(PostActionFactory $factory, PassInterface $configurationPass)
    {
        $this->factory           = $factory;
        $this->configurationPass = $configurationPass;
        $this->propertyAccessor  = PropertyAccess::createPropertyAccessor();
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
                $actionType = $this->getEntityType($actionConfiguration);
                $postAction = $this->factory->create($actionType);

                $actionParameters = $this->getEntityParameters($actionConfiguration);
                if (is_array($actionParameters)) {
                    $passedActionParameters = $this->configurationPass->pass($actionParameters);
                    $this->setActionParameters($postAction, $passedActionParameters);
                }

                $listPostAction->addPostAction($postAction);
            }
        }

        return $listPostAction;
    }

    /**
     * @param PostActionInterface $postAction
     * @param array $actionParameters
     */
    protected function setActionParameters(PostActionInterface $postAction, array $actionParameters)
    {
        foreach ($actionParameters as $parameterName => $parameterValue) {
            if (is_string($parameterName)) {
                $propertyPath = new PropertyPath($parameterName);
                $this->propertyAccessor->setValue($postAction, $propertyPath, $parameterValue);
            }
        }
    }
}
