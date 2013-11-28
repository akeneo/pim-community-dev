<?php

namespace Pim\Bundle\FlexibleEntityBundle\Entity;

use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOptionValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute option values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_flexibleentity_attribute_option_value")
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
