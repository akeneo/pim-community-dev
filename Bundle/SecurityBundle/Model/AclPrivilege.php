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

    /**
     * @return AclPrivilegeIdentity
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @param  AclPrivilegeIdentity $identity
     * @return AclPrivilege
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param  string       $group
     * @return AclPrivilege
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtensionKey()
    {
        return $this->extensionKey;
    }

    /**
     * @param  string       $extensionKey
     * @return AclPrivilege
     */
    public function setExtensionKey($extensionKey)
    {
        $this->extensionKey = $extensionKey;

        return $this;
    }

    /**
     * @return AclPermission[]|ArrayCollection
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @return bool
     */
    public function hasPermissions()
    {
        return !$this->permissions->isEmpty();
    }

    /**
     * @param  AclPermission $permission
     * @return AclPrivilege
     */
    public function addPermission(AclPermission $permission)
    {
        $this->permissions->set($permission->getName(), $permission);

        return $this;
    }

    /**
     * @param AclPermission $permission
     * @return $this
     */
    public function removePermission(AclPermission $permission)
    {
        $this->permissions->removeElement($permission);

        return $this;
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function hasPermission($name)
    {
        return $this->permissions->containsKey($name);
    }

    /**
     * @return int
     */
    public function getPermissionCount()
    {
        return $this->permissions->count();
    }
}
