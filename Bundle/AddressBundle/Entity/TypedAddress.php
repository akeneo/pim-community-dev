<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\AddressBundle\Entity\AddressType;

/**
 * Typed Address
 *
 * @ORM\MappedSuperclass
 */
class TypedAddress extends AddressBase
{
    /**
     * Many-to-many relation field, relation parameters must be in specific class
     *
     * @var Collection
     **/
    protected $types;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_primary", type="boolean", nullable=true)
     * @Soap\ComplexType("boolean", nillable=true)
     */
    protected $primary;

    public function __construct()
    {
        parent::__construct();

        $this->types = new ArrayCollection();
    }

    /**
     * @return Collection
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param AddressType $type
     * @return TypedAddress
     */
    public function addType(AddressType $type)
    {
        if (!$this->getTypes()->contains($type)) {
            $this->getTypes()->add($type);
        }

        return $this;
    }

    /**
     * @param AddressType $type
     * @return TypedAddress
     */
    public function removeType(AddressType $type)
    {
        if ($this->getTypes()->contains($type)) {
            $this->getTypes()->removeElement($type);
        }

        return $this;
    }

    /**
     * @param bool $primary
     * @return TypedAddress
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;

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
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return parent::isEmpty()
            && $this->types->isEmpty()
            && empty($this->primary);
    }
}
