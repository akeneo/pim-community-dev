<?php

namespace Akeneo\UserManagement\Component\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Group implements GroupInterface
{
    /** @var integer */
    protected $id;

    /** @var string */
    protected $name;

    /** @var ArrayCollection */
    protected $roles;

    /**
     * @param string $name [optional] Group name
     */
    public function __construct($name = '')
    {
        $this->name = $name;
        $this->roles = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleLabelsAsString(): string
    {
        $labels = [];
        /** @var $role Role */
        foreach ($this->getRoles() as $role) {
            $labels[] = $role->getLabel();
        }

        return implode(', ', $labels);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getRole($roleName): ?RoleInterface
    {
        /** @var $role Role */
        foreach ($this->getRoles() as $role) {
            if ($roleName == $role->getRole()) {
                return $role;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($role): bool
    {
        if ($role instanceof Role) {
            $roleName = $role->getRole();
        } elseif (is_string($role)) {
            $roleName = $role;
        } else {
            throw new \InvalidArgumentException(
                sprintf('$role must be an instance of %s or a string', Group::class)
            );
        }

        return (bool)$this->getRole($roleName);
    }

    /**
     * {@inheritdoc}
     */
    public function addRole(RoleInterface $role): void
    {
        if (!$this->hasRole($role)) {
            $this->roles->add($role);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($role): void
    {
        if ($role instanceof Role) {
            $roleObject = $role;
        } elseif (is_string($role)) {
            $roleObject = $this->getRole($role);
        } else {
            throw new \InvalidArgumentException(
                sprintf('$role must be an instance of %s or a string', Group::class)
            );
        }
        if ($roleObject) {
            $this->roles->removeElement($roleObject);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setRoles($roles): void
    {
        if ($roles instanceof Collection) {
            $this->roles->clear();

            foreach ($roles as $role) {
                $this->addRole($role);
            }
        } elseif (is_array($roles)) {
            $this->roles = new ArrayCollection($roles);
        } else {
            throw new \InvalidArgumentException(
                '$roles must be an instance of Doctrine\Common\Collections\Collection or an array'
            );
        }
    }

    /**
     * Return the group name field
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->name;
    }
}
