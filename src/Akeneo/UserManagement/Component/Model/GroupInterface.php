<?php

namespace Akeneo\UserManagement\Component\Model;

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
    public function getId(): ?int;

    /**
     * @return string
     */
    public function getName(): ?string;

    /**
     * @param  string $name
     */
    public function setName($name): void;

    /**
     * @return string
     */
    public function getRoleLabelsAsString(): string;

    /**
     * Returns the group roles
     *
     * @return Collection The roles
     */
    public function getRoles(): Collection;

    /**
     * Get role by string
     *
     * @param  string $roleName Role name
     *
     * @return RoleInterface|null
     */
    public function getRole($roleName): ?RoleInterface;

    /**
     * @param  Role|string $role
     *
     * @throws \InvalidArgumentException
     *
     * @return boolean
     */
    public function hasRole($role): bool;

    /**
     * Adds a Role to the Collection
     *
     * @param RoleInterface $role
     */
    public function addRole(RoleInterface $role): void;

    /**
     * Remove the Role object from collection
     *
     * @param  Role|string $role
     *
     * @throws \InvalidArgumentException
     */
    public function removeRole($role): void;

    /**
     * Set new Roles collection
     *
     * @param  array|Collection $roles
     *
     * @throws \InvalidArgumentException
     */
    public function setRoles($roles): void;
}
