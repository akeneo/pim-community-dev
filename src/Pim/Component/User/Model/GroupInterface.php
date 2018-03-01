<?php

namespace Pim\Component\User\Model;

use Doctrine\Common\Collections\Collection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GroupInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param  string $name
     *
     * @return Group
     */
    public function setName($name);

    public function getRoleLabelsAsString();

    /**
     * Returns the group roles
     *
     * @return Collection The roles
     */
    public function getRoles();

    /**
     * Get role by string
     *
     * @param  string $roleName Role name
     *
     * @return Role|null
     */
    public function getRole($roleName);

    /**
     * @param  Role|string $role
     *
     * @throws \InvalidArgumentException
     *
     * @return boolean
     */
    public function hasRole($role);

    /**
     * Adds a Role to the Collection
     *
     * @param  Role $role
     */
    public function addRole(Role $role);

    /**
     * Remove the Role object from collection
     *
     * @param  Role|string $role
     *
     * @throws \InvalidArgumentException
     */
    public function removeRole($role);

    /**
     * Set new Roles collection
     *
     * @param  array|Collection $roles
     *
     * @throws \InvalidArgumentException
     */
    public function setRoles($roles);
}
