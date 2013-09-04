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
    private $rootId;

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
     * @param AclPrivilegeIdentity $identity
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
     * @param string $group
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
    public function getRootId()
    {
        return $this->rootId;
    }

    /**
     * @param string $rootId
     * @return AclPrivilege
     */
    public function setRootId($rootId)
    {
        $this->rootId = $rootId;

        return $this;
    }

    /**
     * @return AclPermission[]
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
     * @param AclPermission $permission
     * @return AclPrivilege
     */
    public function addPermission(AclPermission $permission)
    {
        $this->permissions->add($permission);

        return $this;
    }

    /**
     * @param string $name
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
