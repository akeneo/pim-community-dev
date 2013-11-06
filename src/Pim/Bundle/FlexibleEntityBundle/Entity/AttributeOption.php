<?php

namespace Pim\Bundle\FlexibleEntityBundle\Entity;

use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOption;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Attribute options
 *
 * @ORM\Table(name="pim_flexibleentity_attribute_option")
 * @ORM\Entity(repositoryClass="Pim\Bundle\FlexibleEntityBundle\Entity\Repository\AttributeOptionRepository")
 */
class AttributeOption extends AbstractEntityAttributeOption
{

    /**
     * Overrided to change target entity name
     *
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Attribute", inversedBy="options")
     */
    protected $attribute;

    /**
     * @var ArrayCollection $values
     *
     * @ORM\OneToMany(
     *     targetEntity="AttributeOptionValue", mappedBy="option", cascade={"persist", "remove"}, orphanRemoval=true
     * )
     */
    protected $optionValues;
}
