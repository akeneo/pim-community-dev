<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

/**
 * AddressType
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\AddressBundle\Entity\Repository\AddressTypeRepository")
 * @ORM\Table(name="oro_address_type")
 * @Gedmo\TranslationEntity(class="Oro\Bundle\AddressBundle\Entity\AddressTypeTranslation")
 */
class AddressType implements Translatable
{
    const TYPE_BILLING  = 'billing';
    const TYPE_SHIPPING = 'shipping';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=16)
     * @ORM\Id
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255, unique=true)
     * @Gedmo\Translatable
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $label;

    /**
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Get type name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set address type label
     *
     * @param string $label
     * @return AddressType
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get address type label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return AddressType
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
        return $this->label;
    }
}
