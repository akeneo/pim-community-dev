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

    /**
     * Gets an action name
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->name;
    }

    /**
     * Gets a security group name
     *
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * Gets an action label
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    public function isEnabledAtCreation(): bool
    {
        return $this->isEnabledAtCreation;
    }

    public function __construct(string $name = '', string $group = '', string $label = '', bool $isEnabledAtCreation = true)
    {
        $this->name = $name;
        $this->group = $group;
        $this->label = $label;
        $this->isEnabledAtCreation = $isEnabledAtCreation;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return serialize(
            [
                $this->name,
                $this->group,
                $this->label,
                $this->isEnabledAtCreation,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        list(
            $this->name,
            $this->group,
            $this->label,
            $this->isEnabledAtCreation
            ) = unserialize($serialized);
    }
}
