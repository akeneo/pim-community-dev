<?php

namespace Oro\Bundle\WorkflowBundle\Model\Action;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;

class ActionFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $types;

    /**
     * @param ContainerInterface $container
     * @param array $types
     */
    public function __construct(ContainerInterface $container, array $types = array())
    {
        $this->container = $container;
        $this->types = $types;
    }

    /**
     * @param string $type
     * @param array $options
     * @param ConditionInterface $condition
     * @throws \RunTimeException
     * @return ActionInterface
     */
    public function create($type, array $options = array(), ConditionInterface $condition = null)
    {
        if (!$type) {
            throw new \RunTimeException('The action type must be defined');
        }

        $id = isset($this->types[$type]) ? $this->types[$type] : false;

        if (!$id) {
            throw new \RunTimeException(sprintf('No attached service to action type named `%s`', $type));
        }

        /** @var ActionInterface $action */
        $action = $this->container->get($id);

        if (!$action instanceof ActionInterface) {
            throw new \RunTimeException(sprintf('The service `%s` must implement `ActionInterface`', $id));
        }

        $action->initialize($options);

        if ($condition) {
            $action->setCondition($condition);
        }

        return $action;
    }
}
