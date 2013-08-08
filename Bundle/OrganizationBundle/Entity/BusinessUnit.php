<?php

namespace Oro\Bundle\OrganizationBundle\Entity;

use Oro\Bundle\UserBundle\Entity\User;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use DateTime;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

/**
 * BusinessUnit
 *
 * @ORM\Table("oro_business_unit")
 * @ORM\Entity(repositoryClass="Oro\Bundle\OrganizationBundle\Entity\Repository\BusinessUnitRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 */
class BusinessUnit
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Soap\ComplexType("string", nillable=false)
     * @Oro\Versioned
     */
    protected $name;

    /**
     * @var BusinessUnit
     *
     * @ORM\ManyToOne(targetEntity="BusinessUnit")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     */
    protected $parent;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Soap\ComplexType("string", nillable=false)
     */
    protected $organization;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=100, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     */
    protected $website;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     */
    protected $fax;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $updatedAt;

    /**
     * @var ArrayCollection $tags
     */
    protected $tags;

    /**
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\UserBundle\Entity\User", mappedBy="businessUnits")
     */
    protected $users;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $userOwner;

    /**
     * @var BusinessUnit[]
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\OrganizationBundle\Entity\BusinessUnit")
     * @ORM\JoinTable(name="oro_owner_bu_business_unit",
     *      joinColumns={@ORM\JoinColumn(name="business_unit_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="business_unit_owner_id", referencedColumnName="id",
     *      onDelete="CASCADE")}
     * )
     */
    protected $businessUnitOwners;

    /**
     * @var Organization[]
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinTable(name="oro_owner_business_unit_organization",
     *      joinColumns={@ORM\JoinColumn(name="business_unit_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="organization_owner_id", referencedColumnName="id",
     *      onDelete="CASCADE")}
     * )
     */
    protected $organizationOwners;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return BusinessUnit
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set parent
     *
     * @param BusinessUnit $parent
     * @return BusinessUnit
     */
    public function setParent(BusinessUnit $parent)
    {
        $this->parent = $parent;
    
        return $this;
    }

    /**
     * Get parent
     *
     * @return BusinessUnit
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     * @return BusinessUnit
     */
    public function setOrganization(Organization $organization)
    {
        $this->organization = $organization;
    
        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return BusinessUnit
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    
        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return BusinessUnit
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    
        return $this;
    }

    /**
     * Get website
     *
     * @return string 
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return BusinessUnit
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set fax
     *
     * @param string $fax
     * @return BusinessUnit
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    
        return $this;
    }

    /**
     * Get fax
     *
     * @return string 
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Get user created date/time
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get user last update date/time
     *
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Pre persist event handler
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = $this->createdAt;
    }

    /**
     * Pre update event handler
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers()
    {
        $this->users = $this->users ?: new ArrayCollection();

        return $this->users;
    }

    /**
     * @param ArrayCollection $users
     * @return BusinessUnit
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @param  User $user
     * @return BusinessUnit
     */
    public function addUser(User $user)
    {
        if (!$this->getUsers()->contains($user)) {
            $this->getUsers()->add($user);
        }

        return $this;
    }

    /**
     * @param  User $user
     * @return BusinessUnit
     */
    public function removeUser(User $user)
    {
        if ($this->getUsers()->contains($user)) {
            $this->getUsers()->removeElement($user);
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
     * @return BusinessUnit
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
     * @return BusinessUnit
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
     * @return BusinessUnit
     */
    public function setOrganizationOwners($organizationOwners)
    {
        $this->organizationOwners = $organizationOwners;

        return $this;
    }
}
