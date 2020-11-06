<?php

namespace Oro\Bundle\SecurityBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

class AclPrivilege
{
    /**
     * @var AclPrivilegeIdentity
     */
    private $identity;

    /**
     * @var string
     */
    private $group;

    /**
     * @var string
     */
    private $extensionKey;

    /**
     * @var ArrayCollection
     */
    private $permissions;

    public function __construct()
    {
        $this->permissions = new ArrayCollection();
    }

    public function getIdentity(): \Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity
    {
        return $this->identity;
    }

    /**
     * @param  AclPrivilegeIdentity $identity
     */
    public function setIdentity(\Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity $identity): self
    {
        $this->identity = $identity;

        return $this;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @param  string       $group
     */
    public function setGroup(string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getExtensionKey(): string
    {
        return $this->extensionKey;
    }

    /**
     * @param  string       $extensionKey
     */
    public function setExtensionKey(string $extensionKey): self
    {
        $this->extensionKey = $extensionKey;

        return $this;
    }

    /**
     * @return AclPermission[]|ArrayCollection
     */
    public function getPermissions(): \Doctrine\Common\Collections\ArrayCollection
    {
        return $this->permissions;
    }

    public function hasPermissions(): bool
    {
        return !$this->permissions->isEmpty();
    }

    /**
     * @param  AclPermission $permission
     */
    public function addPermission(AclPermission $permission): self
    {
        $this->permissions->set($permission->getName(), $permission);

        return $this;
    }

    /**
     * @param AclPermission $permission
     * @return $this
     */
    public function removePermission(AclPermission $permission): self
    {
        $this->permissions->removeElement($permission);

        return $this;
    }

    /**
     * @param  string $name
     */
    public function hasPermission(string $name): bool
    {
        return $this->permissions->containsKey($name);
    }

    public function getPermissionCount(): int
    {
        return $this->permissions->count();
    }
}
