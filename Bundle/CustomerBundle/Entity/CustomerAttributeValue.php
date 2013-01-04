<?php
namespace Oro\Bundle\CustomerBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractOrmEntityAttributeValue;
use Oro\Bundle\FlexibleEntityBundle\Entity\OrmEntityAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Value for a customer attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="customer_customer_attribute_value")
 * @ORM\Entity
 */
class CustomerAttributeValue extends AbstractOrmEntityAttributeValue
{
    /**
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\OrmEntityAttribute")
     */
    protected $attribute;

    /**
     * @var Entity $entity
     *
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="values")
     */
    protected $entity;

    /**
     * Store option value, if backend is an option
     *
     * @var AbstractOrmEntityAttributeOption $optionvalue
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\OrmEntityAttributeOption")
     */
    protected $option;
}
