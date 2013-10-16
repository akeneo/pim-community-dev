<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecord;

abstract class AbstractProperty implements PropertyInterface
{
    /** @var array */
    protected $params;

    /**
     * {@inheritdoc}
     */
    public function init(array $params)
    {
        $this->params = $params;
    }

    /**
     * Get param or throws exception
     *
     * @param string $paramName
     *
     * @throws \LogicException
     * @return mixed
     */
    protected function get($paramName)
    {
        if (!isset($this->params[$paramName])) {
            throw new \LogicException(sprintf('Trying to access not existing parameter: "%s"', $paramName));
        }

        return $this->params[$paramName];
    }

    /**
     * Get param if exists or default value
     *
     * @param string $paramName
     * @param null   $default
     *
     * @return mixed
     */
    protected function getOr($paramName, $default = null)
    {
        return isset($this->params[$paramName]) ? $this->params[$paramName] : $default;
    }
}
