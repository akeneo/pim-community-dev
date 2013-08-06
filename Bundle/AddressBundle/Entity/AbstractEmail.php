<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractEmail implements PrimaryItem
{
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $email;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_primary", type="boolean", nullable=true)
     * @Soap\ComplexType("boolean", nillable=true)
     */
    protected $primary = false;

    /**
     * Set email
     *
     * @param string $email
     * @return AbstractEmail
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
     * @param bool $primary
     * @return AbstractEmail
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
        return (string)$this->getEmail();
    }
}
