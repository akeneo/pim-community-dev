<?php
namespace Oro\Bundle\ProductBundle\Entity;

use Oro\Bundle\DataModelBundle\Entity\Mapping\AbstractOrmEntityAttributeValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Value for a product attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="product_attribute_value")
 * @ORM\Entity
 */
class ProductAttributeValue extends AbstractOrmEntityAttributeValue
{
    /**
     * @var Oro\Bundle\DataModelBundle\Model\AbstractEntityAttribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\DataModelBundle\Entity\OrmEntityAttribute")
     */
    protected $attribute;

    /**
     * @var Oro\Bundle\DataModelBundle\Model\AbstractEntity $entity
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="values")
     */
    protected $entity;

    /**
     * Store option value, if backend is an option
     *
     * @var Oro\Bundle\DataModelBundle\Model\AbstractEntityAttributeOption $optionvalue
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\DataModelBundle\Entity\OrmEntityAttributeOption")
     */
    protected $option;
}
