<?php

namespace Oro\Bundle\GridBundle\Action;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Action\RedirectAction;

class ActionFactory implements ActionFactoryInterface
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
        $this->types     = $types;
    }

    /**
     * @param string $name
     * @param string $type
     * @param string|null $aclResource
     * @param array $options
     * @return ActionInterface
     * @throws \RunTimeException
     */
    public function create($name, $type, $aclResource = null, array $options = array())
    {
        if (!$type) {
            throw new \RunTimeException('The type must be defined');
        }

        if (!isset($this->types[$type])) {
            throw new \RunTimeException(sprintf('No attached service to action type named "%s"', $type));
        }

        $actionServiceId = $this->types[$type];

        /** @var $action ActionInterface */
        $action = $this->container->get($actionServiceId);
        $action->setName($name);
        $action->setAclResource($aclResource);
        $action->setOptions($options);

        return $action;
    }
}
