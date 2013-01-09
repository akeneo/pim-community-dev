<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute options
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="oroflexibleentity_attribute_option")
 * @ORM\Entity
 */
class AttributeOption extends AbstractEntityAttributeOption
{

    /**
     * Overrided to change target entity name
     *
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Attribute")
     * @ORM\JoinColumn(name="attribute_id", nullable=false, onDelete="CASCADE", referencedColumnName="id")
     */
    protected $attribute;

    /**
     * @var ArrayCollection $values
     *
     * @ORM\OneToMany(targetEntity="AttributeOptionValue", mappedBy="option", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $optionValues;

}
