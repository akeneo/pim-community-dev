<?php

namespace Oro\Bundle\OrganizationBundle\Entity;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Configurable;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Organization
 *
 * @ORM\Table(name="oro_organization")
 * @ORM\Entity
 * @Configurable(defaultValues={"entity"={"label"="Organization", "plural_label"="Organizations"}})
 */
class Organization
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=3)
     */
    protected $currency;

    /**
     * @var string
     *
     * @ORM\Column(name="currency_precision", type="string", length=10)
     */
    protected $precision;

    /**
     * @var Organization[]
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinTable(name="oro_owner_organization_organization",
     *      joinColumns={@ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="CASCADE")},
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
     * @return Organization
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
     * Set Currency
     *
     * @param string $currency
     * @return Organization
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get Currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set Precision
     *
     * @param string $precision
     * @return Organization
     */
    public function setPrecision($precision)
    {
        $this->precision = $precision;

        return $this;
    }

    /**
     * Get Precision
     *
     * @return string
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return Organization[]
     */
    public function getOwner()
    {
        return $this->organizationOwners;
    }

    /**
     * @param ArrayCollection $organizationOwners
     * @return Organization
     */
    public function setOwner($organizationOwners)
    {
        $this->organizationOwners = $organizationOwners;

        return $this;
    }
}
