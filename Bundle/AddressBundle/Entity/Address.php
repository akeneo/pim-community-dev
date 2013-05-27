<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
 * Address
 *
 * @ORM\Table("oro_address")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Oro\Bundle\AddressBundle\Entity\Repository\AddressRepository")
 */
class Address extends AddressBase
{
    /**
     *  This inheritance needed to add possibility to store address in separate table
     *  http://docs.doctrine-project.org/en/latest/reference/inheritance-mapping.html
     */

    /**
     * @var \Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleValue[]
     *
     * @ORM\OneToMany(targetEntity="Oro\Bundle\AddressBundle\Entity\Value\AddressValue", mappedBy="entity", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Exclude
     */
    protected $values;
}
