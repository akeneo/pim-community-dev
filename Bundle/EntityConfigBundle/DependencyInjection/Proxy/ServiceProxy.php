<?php

namespace Oro\Bundle\EntityConfigBundle\DependencyInjection\Proxy;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ServiceProxy
{
    protected $container;

    protected $service;

    protected $serviceId;

    public function __construct(ContainerInterface $container, $serviceId)
    {
        $this->container = $container;
        $this->serviceId = $serviceId;
    }

    public function getService()
    {
        $this->init();

        return $this->service;
    }

    protected function init()
    {
        if ($this->service === null) {
            $this->service = $this->container->get($this->serviceId);
        }
    }
}
