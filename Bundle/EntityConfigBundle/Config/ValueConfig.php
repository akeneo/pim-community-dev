<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

class ValueConfig implements \Serializable
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string
     */
    protected $value;

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $scope
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->code,
            $this->scope,
            $this->value,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->code,
            $this->scope,
            $this->value,
            ) = unserialize($serialized);
    }
}
