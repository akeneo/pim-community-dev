<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\GridBundle\Datagrid\DatagridManagerInterface;

class DatagridManagerRegistry
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $services = array();

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $name
     * @param string $serviceId
     * @throws \LogicException
     */
    public function addDatagridManagerService($name, $serviceId)
    {
        if (isset($this->services[$name])) {
            throw new \LogicException(sprintf('Datagrid manager with name "%s" already exists', $name));
        }

        $this->services[$name] = $serviceId;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasDatagridManager($name)
    {
        return !empty($this->services[$name]);
    }

    /**
     * @param string $name
     * @return DatagridManagerInterface
     * @throws \LogicException
     */
    public function getDatagridManager($name)
    {
        if (!$this->hasDatagridManager($name)) {
            throw new \LogicException(sprintf('Datagrid manager with name "%s" is not exist', $name));
        }

        $serviceId = $this->services[$name];
        if (!$this->container->has($serviceId)) {
            throw new \LogicException(sprintf('Datagrid manager with service ID "%s" is not exist', $serviceId));
        }

        return $this->container->get($serviceId);
    }
}
