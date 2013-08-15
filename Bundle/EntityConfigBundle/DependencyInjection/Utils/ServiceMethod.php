<?php

namespace Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils;

class ServiceMethod
{
    /**
     * @var mixed
     */
    protected $service;

    /**
     * @var string
     */
    protected $method;

    public function __construct($service, $method)
    {
        $this->service = $service;
        $this->method  = $method;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        return $this->service->{$this->method};
    }
}
