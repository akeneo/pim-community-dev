<?php

namespace Oro\Bundle\SecurityBundle\Metadata;

use Oro\Bundle\SecurityBundle\Acl\Extension\AclClassInfo;

class EntitySecurityMetadata implements AclClassInfo, \Serializable
{
    /**
     * @var string
     */
    protected $securityType;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $group;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string[]
     */
    protected $permissions;

    /**
     * Constructor
     *
     * @param string $securityType
     * @param string $className
     * @param string $group
     * @param string $label
     * @param string[] $permissions
     */
    public function __construct(string $securityType = '', string $className = '', string $group = '', string $label = '', array $permissions = [])
    {
        $this->securityType = $securityType;
        $this->className = $className;
        $this->group = $group;
        $this->label = $label;
        $this->permissions = $permissions;
    }

    /**
     * Gets the security type
     */
    public function getSecurityType(): string
    {
        return $this->securityType;
    }

    /**
     * Gets an entity class name
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
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
     * Gets an entity label
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    public function isEnabledAtCreation(): bool
    {
        return true;
    }

    /**
     * Gets permissions
     *
     * @return string[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return serialize(
            [
                $this->securityType,
                $this->className,
                $this->group,
                $this->label,
                $this->permissions,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        list(
            $this->securityType,
            $this->className,
            $this->group,
            $this->label,
            $this->permissions
            ) = unserialize($serialized);
    }
}
