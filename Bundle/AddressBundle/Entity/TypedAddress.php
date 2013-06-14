<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use JMS\Serializer\Annotation\Exclude;
use Oro\Bundle\AddressBundle\Entity\AddressType;

/**
 * Address
 *
 * @ORM\Table("oro_address_typed")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Oro\Bundle\AddressBundle\Entity\Repository\AddressRepository")
 */
class TypedAddress extends AddressBase
{
    /**
     * @var AddressType
     *
     * @ORM\ManyToOne(targetEntity="AddressType")
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $type;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_default", type="boolean", nullable=true)
     * @Soap\ComplexType("boolean", nillable=true)
     */
    protected $default;

    /**
     * @var \Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleValue[]
     *
     * @ORM\OneToMany(targetEntity="Oro\Bundle\AddressBundle\Entity\Value\AddressValue", mappedBy="entity", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Exclude
     */
    protected $values;

    /**
     * @param AddressType $type
     * @return TypedAddress
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return AddressType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param bool $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }
}
