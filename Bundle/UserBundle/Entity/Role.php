<?php

namespace Oro\Bundle\UserBundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\UserBundle\Entity\Acl;

use JMS\Serializer\Annotation\Type;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * Role Entity
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\UserBundle\Entity\Repository\RoleRepository")
 * @ORM\Table(name="oro_access_role")
 */
class Role implements RoleInterface
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="smallint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     * @Type("integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true, length=30, nullable=false)
     * @Soap\ComplexType("string")
     * @Type("string")
     */
    protected $role;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30)
     * @Soap\ComplexType("string")
     * @Type("string")
     */
    protected $label;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $userOwner;

    /**
     * @var BusinessUnit[]
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\OrganizationBundle\Entity\BusinessUnit")
     * @ORM\JoinTable(name="oro_owner_role_business_unit",
     *      joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="business_unit_owner_id", referencedColumnName="id",
     *      onDelete="CASCADE")}
     * )
     */
    protected $businessUnitOwners;

    /**
     * @var Organization[]
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinTable(name="oro_owner_role_organization",
     *      joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="organization_owner_id", referencedColumnName="id",
     *      onDelete="CASCADE")}
     * )
     */
    protected $organizationOwners;

    /**
     * @ORM\ManyToMany(targetEntity="Acl", mappedBy="accessRoles")
     */
    protected $aclResources;

    /**
     * Populate the role field
     *
     * @param string $role ROLE_FOO etc
     */
    public function __construct($role = '')
    {
        $this->role  =
        $this->label = $role;
        $this->aclResources = new ArrayCollection();
    }

    /**
     * Return the role id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return the role name field
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Return the role label field
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set role name only for newly created role
     *
     * @param  string            $role Role name
     * @return Role
     * @throws \RuntimeException
     */
    public function setRole($role)
    {
        $this->role = (string) strtoupper($role);

        // every role should be prefixed with 'ROLE_'
        if (strpos($this->role, 'ROLE_') !== 0) {
            $this->role = 'ROLE_' . $this->role;
        }

        return $this;
    }

    /**
     * Set the new label for role
     *
     * @param  string $label New label
     * @return Role
     */
    public function setLabel($label)
    {
        $this->label = (string) $label;

        return $this;
    }

    /**
     * Return the role name field
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->role;
    }

    /**
     * Add aclResources
     *
     * @param  Acl  $aclResources
     * @return Role
     */
    public function addAclResource(Acl $aclResources)
    {
        $this->aclResources[] = $aclResources;

        return $this;
    }

    /**
     * Remove aclResources
     *
     * @param Acl $aclResources
     */
    public function removeAclResource(Acl $aclResources)
    {
        $this->aclResources->removeElement($aclResources);
    }

    /**
     * Get aclResources
     *
     * @return ArrayCollection
     */
    public function getAclResources()
    {
        return $this->aclResources;
    }

    public function setAclResources($resources)
    {
        $this->aclResources = $resources;
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
     * @return Role
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
     * @return Role
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
     * @return Role
     */
    public function setOrganizationOwners($organizationOwners)
    {
        $this->organizationOwners = $organizationOwners;

        return $this;
    }
}
