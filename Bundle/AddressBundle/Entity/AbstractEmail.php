<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\FormBundle\Entity\PrimaryItem;
use Oro\Bundle\FormBundle\Entity\EmptyItem;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractEmail implements PrimaryItem, EmptyItem
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
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $email;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_primary", type="boolean", nullable=true)
     * @Soap\ComplexType("boolean", nillable=true)
     */
    protected $primary;

    /**
     * @param string|null $email
     */
    public function __construct($email = null)
    {
        $this->email = $email;
        $this->primary = false;
    }

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

    /**
     * Check if entity is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->email);
    }
}
