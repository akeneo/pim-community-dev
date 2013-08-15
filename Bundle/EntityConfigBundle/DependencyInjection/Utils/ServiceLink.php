<?php

namespace Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This class contain link to the service
 * It may be useful if you get circular error in DI
 */
class ServiceLink
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var mixed
     */
    protected $service;

    /**
     * @var string
     */
    protected $serviceId;

    public function __construct(ContainerInterface $container, $serviceId)
    {
        $this->container = $container;
        $this->serviceId = $serviceId;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        $this->init();

        return $this->service;
    }

    /**
     * Init service to internal cache
     */
    protected function init()
    {
        if ($this->service === null) {
            $this->service = $this->container->get($this->serviceId);
        }
    }
}
