<?php
namespace Oro\Bundle\ProductBundle\Entity;

use Oro\Bundle\DataModelBundle\Model\AbstractOrmEntity;
use Oro\Bundle\DataModelBundle\Entity\AbstractOrmEntityAttributeValue;
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
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="ProductAttribute")
     */
    protected $attribute;

    /**
     * @var Entity $entity
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="values")
     */
    protected $entity;

}
