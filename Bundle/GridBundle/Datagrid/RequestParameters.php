<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestParameters implements ParametersInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    protected $rootParameterName;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected static $usedParameterTypes = array(
        self::FILTER_PARAMETERS,
        self::PAGER_PARAMETERS,
        self::SORT_PARAMETERS,
        self::ADDITIONAL_PARAMETERS
    );

    /**
     * @param ContainerInterface $container
     * @param string             $rootParameterName
     */
    public function __construct(ContainerInterface $container, $rootParameterName)
    {
        $this->container = $container;
        $this->rootParameterName = $rootParameterName;
    }

    /**
     * Get parameter value from parameters container
     *
     * @param  string $type
     * @param  mixed  $default
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
     * @return void
     */
    public function set($type, $value)
    {
        $parameters = $this->getRootParameterValue();
        $currentValue = $this->get($type);

        if (is_array($currentValue) && is_array($value)) {
            $parameters[$type] = array_merge_recursive($currentValue, $value);
        } else {
            $parameters[$type] = $value;
        }

        $this->getRequest()->query->set($this->rootParameterName, $parameters);
    }

    /**
     * @return array
     */
    protected function getRootParameterValue()
    {
        return $this->getRequest()->get($this->rootParameterName, array());
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

    /**
     * @return array
     */
    public function toArray()
    {
        $result = array($this->rootParameterName => array());

        foreach (self::$usedParameterTypes as $type) {
            $value = $this->get($type, array());
            if (!empty($value)) {
                $result[$this->rootParameterName][$type] = $value;
            }
        }

        return $result;
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
        $rootValue = $this->getRootParameterValue();

        return isset($rootValue[self::SCOPE_PARAMETER]) ? $rootValue[self::SCOPE_PARAMETER] : null;
    }
}
