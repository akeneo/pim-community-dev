<?php

namespace Oro\Bundle\EntityConfigBundle\Config\Id;

class EntityConfigId implements EntityConfigIdInterface
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
    public function getId()
    {
        return sprintf('entity_%s_%s', $this->scope, strtr($this->className, '\\', '-'));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'Config for Entity "%s" in scope "%s"',
            $this->getClassName(),
            $this->getScope()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->className,
            $this->scope,
        ));
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
