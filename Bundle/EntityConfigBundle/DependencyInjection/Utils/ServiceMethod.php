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

    /**
     * @var array
     */
    protected $arguments;

    public function __construct()
    {
        $this->arguments = func_get_args();
    }

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        return call_user_func_array(array($this->service, $this->method), $this->arguments);
    }

    /**
     * @return mixed
     */
    public function __invoke()
    {
        return $this->execute();
    }
}
