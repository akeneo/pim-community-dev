<?php
namespace Oro\Bundle\ProductBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractOrmEntityAttributeValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Value for a product attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="product_product_attribute_value")
 * @ORM\Entity
 */
class ProductAttributeValue extends AbstractOrmEntityAttributeValue
{
    /**
     * @var Oro\Bundle\FlexibleEntityBundle\Model\Entity\AbstractEntityAttribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\OrmEntityAttribute")
     */
    protected $attribute;

    /**
     * @var Oro\Bundle\FlexibleEntityBundle\Model\Entity\AbstractEntity $entity
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="values")
     */
    protected $entity;

    /**
     * Store option value, if backend is an option
     *
     * @var Oro\Bundle\FlexibleEntityBundle\Model\Entity\AbstractEntityAttributeOption $optionvalue
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\OrmEntityAttributeOption")
     */
    protected $option;
}
