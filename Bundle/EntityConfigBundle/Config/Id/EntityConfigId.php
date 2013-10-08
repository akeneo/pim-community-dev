<?php

namespace Oro\Bundle\EntityConfigBundle\Config\Id;

class EntityConfigId implements ConfigIdInterface
{
    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string
     */
    protected $className;

    public function __construct($className, $scope)
    {
        $this->className = $className;
        $this->scope     = $scope;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }


    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return sprintf('entity_%s_%s', $this->scope, strtr($this->className, '\\', '-'));
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            array(
                $this->className,
                $this->scope,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->className,
            $this->scope,
            ) = unserialize($serialized);
    }
}
