<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractPhone implements PrimaryItem
{
    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $phone;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_primary", type="boolean", nullable=true)
     * @Soap\ComplexType("boolean", nillable=true)
     */
    protected $primary = false;

    /**
     * Set phone
     *
     * @param string $phone
     * @return AbstractPhone
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
     * @param bool $primary
     * @return AbstractPhone
     */
    public function setPrimary($primary)
    {
        $this->primary = (bool)$primary;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrimary()
    {
        return $this->primary;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getPhone();
    }
}
