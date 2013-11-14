<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;

class ConditionFactory
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
     * @param null|string $message
     * @throws \RunTimeException
     * @return ConditionInterface
     */
    public function create($type, array $options = array(), $message = null)
    {
        if (!$type) {
            throw new \RunTimeException('The type must be defined');
        }

        $id = isset($this->types[$type]) ? $this->types[$type] : false;

        if (!$id) {
            throw new \RunTimeException(sprintf('No attached service to condition type named `%s`', $type));
        }

        /** @var ConditionInterface $condition */
        $condition = $this->container->get($id);

        if (!$condition instanceof ConditionInterface) {
            throw new \RunTimeException(sprintf('The service `%s` must implement `ConditionInterface`', $id));
        }

        if ($message) {
            $condition->setMessage($message);
        }
        $condition->initialize($options);

        return $condition;
    }
}
