<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\ExecutionContext;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\LocaleBundle\Model\FullNameInterface;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;

use Oro\Bundle\FormBundle\Entity\EmptyItem;

/**
 * Address
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
abstract class AbstractAddress implements EmptyItem, FullNameInterface, AddressInterface
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
     * @ORM\Column(name="label", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $label;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=500)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $street;

    /**
     * @var string
     *
     * @ORM\Column(name="street2", type="string", length=500, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $street2;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", type="string", length=20)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $postalCode;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Country", cascade={"persist"})
     * @ORM\JoinColumn(name="country_code", referencedColumnName="iso2_code")
     * @Soap\ComplexType("string", nillable=false)
     */
    protected $country;

    /**
     * @var Region
     *
     * @TODO Refactor in CRM-185
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Region", cascade={"persist"})
     * @ORM\JoinColumn(name="region_code", referencedColumnName="combined_code")
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $state;

    /**
     * @var string
     *
     * @TODO Refactor in CRM-185
     * @ORM\Column(name="organization", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $organization;

    /**
     * @var string
     *
     * @TODO Refactor in CRM-185
     * @ORM\Column(name="state_text", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $stateText;

    /**
     * @var string
     *
     * @ORM\Column(name="name_prefix", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $namePrefix;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="middle_name", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $middleName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="name_suffix", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $nameSuffix;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \DateTime $updated
     *
     * @ORM\Column(type="datetime")
     */
    protected $updated;

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
     * Set id
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return AbstractAddress
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set street
     *
     * @param string $street
     * @return AbstractAddress
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set street2
     *
     * @param string $street2
     * @return AbstractAddress
     */
    public function setStreet2($street2)
    {
        $this->street2 = $street2;

        return $this;
    }

    /**
     * Get street2
     *
     * @return string
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return AbstractAddress
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @TODO Refactor in CRM-185
     * @deprecated Use setRegion
     * @param Region $region
     * @return AbstractAddress
     */
    public function setState($region)
    {
        $this->setRegion($region);

        return $this;
    }

    /**
     * Set region
     *
     * @param Region $region
     * @return AbstractAddress
     */
    public function setRegion($region)
    {
        $this->state = $region;

        return $this;
    }

    /**
     * Get state
     *
     * @TODO Refactor in CRM-185
     * @deprecated Use getRegion
     * @return Region
     */
    public function getState()
    {
        return $this->getRegion();
    }

    /**
     * Get region
     *
     * @return Region
     */
    public function getRegion()
    {
        return $this->state;
    }

    /**
     * Set state text
     *
     * @TODO Refactor in CRM-185
     * @deprecated Use setRegionText
     * @param string $regionText
     * @return AbstractAddress
     */
    public function setStateText($regionText)
    {
        $this->setRegionText($regionText);

        return $this;
    }

    /**
     * Set region text
     *
     * @param string $regionText
     * @return AbstractAddress
     */
    public function setRegionText($regionText)
    {
        $this->stateText = $regionText;

        return $this;
    }

    /**
     * Get state test
     *
     * @TODO Refactor in CRM-185
     * @deprecated Use getRegionText
     * @return string
     */
    public function getStateText()
    {
        return $this->getRegionText();
    }

    /**
     * Get region test
     *
     * @return string
     */
    public function getRegionText()
    {
        return $this->stateText;
    }

    /**
     * Get name of region
     *
     * @return string
     */
    public function getRegionName()
    {
        return $this->getRegion() ? $this->getRegion()->getName() : $this->getRegionText();
    }

    /**
     * Get code of region
     *
     * @return string
     */
    public function getRegionCode()
    {
        return $this->getRegion() ? $this->getRegion()->getCode() : '';
    }

    /**
     * Get state
     *
     * @TODO Refactor in CRM-185
     * @deprecated Use getUniversalRegion
     * @return Region|string
     */
    public function getUniversalState()
    {
        return $this->getUniversalRegion();
    }

    /**
     * Get region or region string
     *
     * @return Region|string
     */
    public function getUniversalRegion()
    {
        if (!empty($this->stateText)) {
            return $this->stateText;
        } else {
            return $this->state;
        }
    }

    /**
     * Set postal_code
     *
     * @param string $postalCode
     * @return AbstractAddress
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postal_code
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set country
     *
     * @param Country $country
     * @return AbstractAddress
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Get name of country
     *
     * @return string
     */
    public function getCountryName()
    {
        return $this->getCountry() ? $this->getCountry()->getName() : '';
    }

    /**
     * Get country ISO3 code
     *
     * @return string
     */
    public function getCountryIso3()
    {
        return $this->getCountry() ? $this->getCountry()->getIso3Code() : '';
    }

    /**
     * Get country ISO2 code
     *
     * @return string
     */
    public function getCountryIso2()
    {
        return $this->getCountry() ? $this->getCountry()->getIso2Code() : '';
    }

    /**
     * Sets organization
     *
     * @param string $organization
     * @return AbstractAddress
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**

     * Set name prefix
     *
     * @param string $namePrefix
     * @return $this
     */
    public function setNamePrefix($namePrefix)
    {
        $this->namePrefix = $namePrefix;

        return $this;
    }

    /**
     * Get name prefix
     *
     * @return string
     */
    public function getNamePrefix()
    {
        return $this->namePrefix;
    }

    /**

     * Set first name
     *
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**

     * Set middle name
     *
     * @param string $middleName
     * @return $this
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * Get middle name
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * Set last name
     *
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set name suffix
     *
     * @param string $nameSuffix
     * @return $this
     */
    public function setNameSuffix($nameSuffix)
    {
        $this->nameSuffix = $nameSuffix;

        return $this;
    }

    /**
     * Get name suffix
     *
     * @return string
     */
    public function getNameSuffix()
    {
        return $this->nameSuffix;
    }

    /**
     * Get address created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created;
    }

    /**
     * Set address created date/time
     *
     * @param \DateTime $created
     */
    public function setCreatedAt($created)
    {
        $this->created = $created;
    }

    /**
     * Get address last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated;
    }

    /**
     * Set address updated date/time
     *
     * @param \DateTime $updated
     */
    public function setUpdatedAt($updated)
    {
        $this->updated = $updated;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->created = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updated = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @TODO Refactor in CRM-185
     * @deprecated Use isRegionValid
     * @param ExecutionContext $context
     */
    public function isStateValid(ExecutionContext $context)
    {
        $this->isRegionValid($context);
    }

    public function isRegionValid(ExecutionContext $context)
    {
        if ($this->getCountry() && $this->getCountry()->hasRegions() && !$this->state) {
            $propertyPath = $context->getPropertyPath() . '.state';
            $context->addViolationAt(
                $propertyPath,
                'State is required for country %country%',
                array('%country%' => $this->getCountry()->getName())
            );
        }
    }

    /**
     * Convert address to string
     * @todo: Address format must be used here
     *
     * @return string
     */
    public function __toString()
    {
        $data = array(
            $this->getFirstName(),
            $this->getLastName(),
            ',',
            $this->getStreet(),
            $this->getStreet2(),
            $this->getCity(),
            $this->getUniversalRegion(),
            ',',
            $this->getCountry(),
            $this->getPostalCode(),
        );

        $str = implode(' ', $data);
        $check = trim(str_replace(',', '', $str));
        return empty($check) ? '' : $str;
    }

    /**
     * Check if entity is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->label)
            && empty($this->firstName)
            && empty($this->lastName)
            && empty($this->street)
            && empty($this->street2)
            && empty($this->city)
            && empty($this->state)
            && empty($this->stateText)
            && empty($this->country)
            && empty($this->postalCode);
    }

    /**
     * @param mixed $other
     * @return bool
     */
    public function isEqual($other)
    {
        $class = get_class($this);

        if (!$other instanceof $class) {
            return false;
        }

        /** @var AbstractAddress $other */
        if ($this->getId() && $other->getId()) {
            return $this->getId() == $other->getId();
        }

        if ($this->getId() || $other->getId()) {
            return false;
        }

        return $this === $other;
    }
}
