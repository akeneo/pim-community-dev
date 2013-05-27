<?php

namespace Oro\Bundle\AddressBundle\Entity;

use JMS\Serializer\Annotation\Exclude;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

/**
 * Country
 *
 * @ORM\Table("oro_dictionary_country")
 * @ORM\Entity
 */
class Country
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="iso2_code", type="string", length=2)
     * @Soap\ComplexType("string", nillable=true)
     */
    private $iso2Code;

    /**
     * @var string
     *
     * @ORM\Column(name="iso3_code", type="string", length=3)
     * @Soap\ComplexType("string", nillable=true)
     */
    private $iso3Code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     * @Soap\ComplexType("string", nillable=true)
     */
    private $name;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Region", mappedBy="country", cascade={"ALL"}, fetch="EXTRA_LAZY")
     * @Exclude
     */
    private $regions;

    /**
     * @param null|string $name
     * @param null|string $iso2Code
     * @param null|string $iso3Code
     */
    public function __construct($name = null, $iso2Code = null, $iso3Code = null)
    {
        $this->setName($name);
        $this->setIso2Code($iso2Code);
        $this->setIso3Code($iso3Code);
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $regions
     *
     * @return $this
     */
    public function setRegions($regions)
    {
        $this->regions = $regions;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getRegions()
    {
        return $this->regions;
    }

    /**
     * Check if country contains regions
     *
     * @return bool
     */
    public function hasRegions()
    {
        return count($this->regions) > 0;
    }

    /**
     * Set iso2_code
     *
     * @param string $iso2Code
     * @return Country
     */
    public function setIso2Code($iso2Code)
    {
        $this->iso2Code = $iso2Code;
    
        return $this;
    }

    /**
     * Get iso2_code
     *
     * @return string 
     */
    public function getIso2Code()
    {
        return $this->iso2Code;
    }

    /**
     * Set iso3_code
     *
     * @param string $iso3Code
     * @return Country
     */
    public function setIso3Code($iso3Code)
    {
        $this->iso3Code = $iso3Code;
    
        return $this;
    }

    /**
     * Get iso3_code
     *
     * @return string 
     */
    public function getIso3Code()
    {
        return $this->iso3Code;
    }

    /**
     * Set country name
     *
     * @param string $name
     * @return Country
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get country name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
