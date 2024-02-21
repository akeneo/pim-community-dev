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
     * true if the ACL must be visible in the UI. eg: the edit role permissions screen
     * ACL that are not visible still exist and can be managed by the code.
     */
    protected bool $visible = true;

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

    public function __construct(
        $name = '',
        $group = '',
        $label = '',
        bool $isEnabledAtCreation = true,
        int $order = 0,
        bool $visible = true
    ) {
        $this->name = $name;
        $this->group = $group;
        $this->label = $label;
        $this->isEnabledAtCreation = $isEnabledAtCreation;
        $this->order = $order;
        $this->visible = $visible;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function isVisible(): bool
    {
        return $this->visible;
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
                $this->visible,
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
            $this->visible,
        ) = unserialize($serialized);
    }
}
