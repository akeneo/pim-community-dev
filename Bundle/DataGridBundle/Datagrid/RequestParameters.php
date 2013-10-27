<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Symfony\Component\HttpFoundation\Request;

class RequestParameters
{
    const ADDITIONAL_PARAMETERS = '_parameters';
    /** @TODO MOVE TO FLEXIBLE */
    const SCOPE_PARAMETER = '_scope';

    const DEFAULT_ROOT_PARAM = 'grid';

    /** @var string */
    protected $rootParam;

    /** @var Request */
    protected $request;

    /**
     * Get parameter value from parameters container
     *
     * @param  string $type
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function get($type, $default = [])
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

        $this->getRequest()->query->set($this->rootParam ? : self::DEFAULT_ROOT_PARAM, $parameters);
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
     * @param $rootParam
     *
     * @return $this
     */
    public function setRootParameter($rootParam)
    {
        $this->rootParam = $rootParam;

        return $this;
    }

    /**
     * @return array
     */
    protected function getRootParameterValue()
    {
        return $this->getRequest()->get($this->rootParam ? : self::DEFAULT_ROOT_PARAM, []);
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        if ($request instanceof Request) {
            $this->request = clone $request;
        }
    }
}
