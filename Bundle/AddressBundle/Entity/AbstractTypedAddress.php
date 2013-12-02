<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\FormBundle\Entity\PrimaryItem;

/**
 * Typed Address
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractTypedAddress extends AbstractAddress implements PrimaryItem
{
    /**
     * Many-to-many relation field, relation parameters must be in specific class
     *
     * @var Collection
     *
     * @Soap\ComplexType("Oro\Bundle\AddressBundle\Entity\AddressType[]", nillable=true)
     */
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
        $this->types = new ArrayCollection();
        $this->primary = false;
    }

    /**
     * @return Collection|AddressType[]
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Get list of address types names
     *
     * @return array
     */
    public function getTypeNames()
    {
        $result = array();
        /** @var AddressType $type */
        foreach ($this->getTypes() as $type) {
            $result[] = $type->getName();
        }
        return $result;
    }

    /**
     * Gets instance of address type entity by it's name if it exist.
     *
     * @param string $typeName
     * @return AddressType|null
     */
    public function getTypeByName($typeName)
    {
        foreach ($this->getTypes() as $type) {
            if ($type->getName() === $typeName) {
                return $type;
            }
        }
        return null;
    }

    /**
     * Checks if address has type with specified name
     *
     * @param string $typeName
     * @return bool
     */
    public function hasTypeWithName($typeName)
    {
        return null !== $this->getTypeByName($typeName);
    }

    /**
     * Get list of address types names
     *
     * @return array
     */
    public function getTypeLabels()
    {
        $result = array();

        foreach ($this->getTypes() as $type) {
            $result[] = $type->getLabel();
        }

        return $result;
    }

    /**
     * @param AddressType $type
     * @return AbstractTypedAddress
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
     * @return AbstractTypedAddress
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
     * @return AbstractTypedAddress
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
        return (bool)$this->primary;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return parent::isEmpty()
            && $this->types->isEmpty()
            && !$this->primary;
    }
}
