<?php

namespace Oro\Bundle\SecurityBundle\Metadata;

use Oro\Bundle\SecurityBundle\Acl\Extension\AclClassInfo;

class ActionMetadata implements AclClassInfo, \Serializable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $group;

    /**
     * @var string
     */
    protected $label;

    /** @var bool */
    protected $isEnabledByDefault;

    /**
     * Gets an action name
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->name;
    }

    /**
     * Gets a security group name
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Gets an action label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function isEnabledByDefault(): bool
    {
        return $this->isEnabledByDefault;
    }

    public function __construct($name = '', $group = '', $label = '', bool $isEnabledByDefault = true)
    {
        $this->name = $name;
        $this->group = $group;
        $this->label = $label;
        $this->isEnabledByDefault = $isEnabledByDefault;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            [
                $this->name,
                $this->group,
                $this->label,
                $this->isEnabledByDefault,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->name,
            $this->group,
            $this->label,
            $this->isEnabledByDefault
            ) = unserialize($serialized);
    }
}
