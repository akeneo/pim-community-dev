<?php

namespace Oro\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Configurable;
use Oro\Bundle\EntityExtendBundle\Metadata\Annotation\Extend;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * @ORM\Entity(repositoryClass="Oro\Bundle\UserBundle\Entity\Repository\GroupRepository")
 * @ORM\Table(name="oro_access_group")
 * @Configurable(
 *      routeName="oro_user_group_index",
 *      defaultValues={"entity"={"icon"="group","label"="Group", "plural_label"="Groups"}}
 * )
 * @Extend
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\Column(type="smallint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     * @Type("integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true, length=30, nullable=false)
     * @Soap\ComplexType("string")
     * @Type("string")
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="oro_user_access_group_role",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Soap\ComplexType("int[]")
     * @Exclude
     */
    protected $roles;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @Exclude
     */
    protected $userOwner;

    /**
     * @var BusinessUnit[]
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\OrganizationBundle\Entity\BusinessUnit")
     * @ORM\JoinTable(name="oro_owner_group_business_unit",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="business_unit_owner_id", referencedColumnName="id",
     *      onDelete="CASCADE")}
     * )
     */
    protected $businessUnitOwners;

    /**
     * @var Organization[]
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinTable(name="oro_owner_group_organization",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="organization_owner_id", referencedColumnName="id",
     *      onDelete="CASCADE")}
     * )
     */
    protected $organizationOwners;

    /**
     * @param string $name [optional] Group name
     */
    public function __construct($name = '')
    {
        $this->name  = $name;
        $this->roles = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string $name
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getRoleLabelsAsString()
    {
        $labels = array();
        /** @var $role Role */
        foreach ($this->getRoles() as $role) {
            $labels[] = $role->getLabel();
        }

        return implode(', ', $labels);
    }

    /**
     * Returns the group roles
     *
     * @return Collection The roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Get role by string
     *
     * @param  string    $roleName Role name
     * @return Role|null
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
     * @param  Role|string               $role
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function hasRole($role)
    {
        if ($role instanceof Role) {
            $roleName = $role->getRole();
        } elseif (is_string($role)) {
            $roleName = $role;
        } else {
            throw new \InvalidArgumentException(
                '$role must be an instance of Oro\Bundle\UserBundle\Entity\Role or a string'
            );
        }

        return (bool) $this->getRole($roleName);
    }

    /**
     * Adds a Role to the Collection
     *
     * @param  Role  $role
     * @return Group
     */
    public function addRole(Role $role)
    {
        if (!$this->hasRole($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * Remove the Role object from collection
     *
     * @param  Role|string               $role
     * @return Group
     * @throws \InvalidArgumentException
     */
    public function removeRole($role)
    {
        if ($role instanceof Role) {
            $roleObject = $role;
        } elseif (is_string($role)) {
            $roleObject = $this->getRole($role);
        } else {
            throw new \InvalidArgumentException(
                '$role must be an instance of Oro\Bundle\UserBundle\Entity\Role or a string'
            );
        }
        if ($roleObject) {
            $this->roles->removeElement($roleObject);
        }

        return $this;
    }

    /**
     * Set new Roles collection
     *
     * @param  array|Collection          $roles
     * @return Group
     * @throws \InvalidArgumentException
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
     * @return User
     */
    public function getUserOwner()
    {
        return $this->userOwner;
    }

    /**
     * @param User $userOwner
     * @return Group
     */
    public function setUserOwner(User $userOwner)
    {
        $this->userOwner = $userOwner;

        return $this;
    }

    /**
     * @return BusinessUnit[]
     */
    public function getBusinessUnitOwners()
    {
        return $this->businessUnitOwners;
    }

    /**
     * @param ArrayCollection $businessUnitOwners
     * @return Group
     */
    public function setBusinessUnitOwners($businessUnitOwners)
    {
        $this->businessUnitOwners = $businessUnitOwners;

        return $this;
    }

    /**
     * @return Organization[]
     */
    public function getOrganizationOwners()
    {
        return $this->organizationOwners;
    }

    /**
     * @param ArrayCollection $organizationOwners
     * @return Group
     */
    public function setOrganizationOwners($organizationOwners)
    {
        $this->organizationOwners = $organizationOwners;

        return $this;
    }
}
