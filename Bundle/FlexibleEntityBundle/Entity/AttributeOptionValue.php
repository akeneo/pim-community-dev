<?php

namespace Oro\Bundle\FlexibleEntityBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOptionValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute option values
 *
 * @ORM\Table(name="oro_flexibleentity_attribute_option_value")
 * @ORM\Entity
 */
class AttributeOptionValue extends AbstractEntityAttributeOptionValue
{

    /**
     * Overrided to change target option name
     *
     * @var AttributeOption $option
     *
     * @ORM\ManyToOne(targetEntity="AttributeOption", inversedBy="optionValues")
     * @ORM\JoinColumn(name="option_id", nullable=false, onDelete="CASCADE", referencedColumnName="id")
     */
    protected $option;
}
