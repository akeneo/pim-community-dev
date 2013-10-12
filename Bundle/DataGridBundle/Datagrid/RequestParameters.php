<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RequestParameters
{
    const ADDITIONAL_PARAMETERS = '_parameters';
    /** @TODO MOVE TO FLEXIBLE */
    const SCOPE_PARAMETER = '_scope';

    const ROOT_PARAM = 'grid';

    /** @var ContainerInterface */
    private $container;

    /** @var Request */
    protected $request;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get parameter value from parameters container
     *
     * @param  string $type
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function get($type, $default = array())
    {
        $rootParameter = $this->getRootParameterValue();

        return isset($rootParameter[$type]) ? $rootParameter[$type] : $default;
    }

    /**
     * @param  string $type
     * @param  mixed  $value
     *
     * @return void
     */
    public function set($type, $value)
    {
        $parameters   = $this->getRootParameterValue();
        $currentValue = $this->get($type);

        if (is_array($currentValue) && is_array($value)) {
            $parameters[$type] = array_merge_recursive($currentValue, $value);
        } else {
            $parameters[$type] = $value;
        }

        $this->getRequest()->query->set(self::ROOT_PARAM, $parameters);
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->getRequest()->getLocale();
    }

    /**
     * @return string
     */
    public function getScope()
    {
        /** @TODO MOVE TO FLEXIBLE */
        $rootValue = $this->getRootParameterValue();

        return isset($rootValue[self::SCOPE_PARAMETER]) ? $rootValue[self::SCOPE_PARAMETER] : null;
    }

    /**
     * @return array
     */
    protected function getRootParameterValue()
    {
        return $this->getRequest()->get(self::ROOT_PARAM, array());
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        if (!$this->request) {
            $this->request = clone $this->container->get('request');
        }

        return $this->request;
    }
}
