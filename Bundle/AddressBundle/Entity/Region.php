<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

use Oro\Bundle\AddressBundle\Entity\Country;

/**
 * Region
 *
 * @ORM\Table("oro_dictionary_region", indexes={
 *      @ORM\Index(name="region_name_idx", columns={"name"})
 * })
 * @ORM\Entity(repositoryClass="Oro\Bundle\AddressBundle\Entity\Repository\RegionRepository")
 * @Gedmo\TranslationEntity(class="Oro\Bundle\AddressBundle\Entity\RegionTranslation")
 */
class Region implements Translatable
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="combined_code", type="string", length=16)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $combinedCode;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="regions", cascade={"persist"})
     * @ORM\JoinColumn(name="country_code", referencedColumnName="iso2_code")
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $country;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=32)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Soap\ComplexType("string", nillable=true)
     * @Gedmo\Translatable
     */
    protected $name;

    /**
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * @param string $combinedCode
     */
    public function __construct($combinedCode)
    {
        $this->combinedCode = $combinedCode;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getCombinedCode()
    {
        return $this->combinedCode;
    }

    /**
     * Set country
     *
     * @param  Country $country
     * @return Region
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
     * Set code
     *
     * @param  string $code
     * @return Region
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return Region
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
     * Set locale
     *
     * @param string $locale
     * @return Region
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Returns locale code
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
    }
}
