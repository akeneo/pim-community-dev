<?php

namespace Pim\Component\User\Model;

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleLabelsAsString()
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
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getRole($roleName)
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
    public function hasRole($role)
    {
        if ($role instanceof Role) {
            $roleName = $role->getRole();
        } elseif (is_string($role)) {
            $roleName = $role;
        } else {
            throw new \InvalidArgumentException(
                '$role must be an instance of Pim\Component\User\Model\Group or a string'
            );
        }

        return (bool)$this->getRole($roleName);
    }

    /**
     * {@inheritdoc}
     */
    public function addRole(Role $role)
    {
        if (!$this->hasRole($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($role)
    {
        if ($role instanceof Role) {
            $roleObject = $role;
        } elseif (is_string($role)) {
            $roleObject = $this->getRole($role);
        } else {
            throw new \InvalidArgumentException(
                '$role must be an instance of Pim\Component\User\Model\Group or a string'
            );
        }
        if ($roleObject) {
            $this->roles->removeElement($roleObject);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRoles($roles)
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

        return $this;
    }

    /**
     * Return the group name field
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
    }
}
