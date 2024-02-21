<?php

namespace Oro\Bundle\SecurityBundle\DependencyInjection\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This class contain link to the service
 * It may be useful if you get circular reference error in DI
 */
class ServiceLink
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $serviceId;

    /**
     * @var string
     */
    protected $isOptional;

    /**
     * @var mixed
     */
    protected $service = false;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param string $serviceId
     * @param bool $isOptional
     */
    public function __construct(ContainerInterface $container, $serviceId, $isOptional = false)
    {
        $this->container = $container;
        $this->serviceId = $serviceId;
        $this->isOptional = $isOptional;
    }

    /**
     * Gets a service this link refers to
     *
     * @return object
     */
    public function getService()
    {
        // try to get a service and save it to internal cache
        if ($this->service === false) {
            $this->service = !$this->isOptional || $this->container->has($this->serviceId)
                ? $this->container->get($this->serviceId)
                : null;
        }

        return $this->service;
    }
}
