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

    /**
     * Defines if the ACL must be enabled/disabled at creation for all roles.
     *
     * @var bool
     */
    protected $isEnabledAtCreation;

    protected int $order = 0;

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

    public function isEnabledAtCreation(): bool
    {
        return $this->isEnabledAtCreation;
    }

    public function __construct($name = '', $group = '', $label = '', bool $isEnabledAtCreation = true, int $order = 0)
    {
        $this->name = $name;
        $this->group = $group;
        $this->label = $label;
        $this->isEnabledAtCreation = $isEnabledAtCreation;
        $this->order = $order;
    }

    public function getOrder(): int
    {
        return $this->order;
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
                $this->isEnabledAtCreation,
                $this->order,
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
            $this->isEnabledAtCreation,
            $this->order,
            ) = unserialize($serialized);
    }
}
