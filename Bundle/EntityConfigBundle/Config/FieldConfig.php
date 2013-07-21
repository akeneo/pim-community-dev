<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

class FieldConfig extends AbstractConfig implements FieldConfigInterface
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $values = array();

    public function __construct($className, $code, $type, $scope)
    {
        $this->className = $className;
        $this->code      = $code;
        $this->type      = $type;
        $this->scope     = $scope;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

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
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->code,
            $this->className,
            $this->type,
            $this->scope,
            $this->values,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->code,
            $this->className,
            $this->type,
            $this->scope,
            $this->values,
            ) = unserialize($serialized);
    }

    /**
     * Clone Config
     */
    public function __clone()
    {
        $this->values = array_map(function ($value) {
            if (is_object($value)) {
                return clone $value;
            } else {
                return $value;
            }
        }, $this->values);

    }
}
