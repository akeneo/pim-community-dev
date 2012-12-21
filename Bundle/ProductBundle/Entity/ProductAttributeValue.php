<?php
namespace Oro\Bundle\ProductBundle\Entity;

use Oro\Bundle\DataModelBundle\Model\Entity as AbstractEntity;
use Oro\Bundle\DataModelBundle\Entity\EntityAttributeValue as AbstractEntityAttributeValue;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * Value for a product attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="product_attribute_value")
 * @ORM\Entity
 * @Gedmo\TranslationEntity(class="Oro\Bundle\ProductBundle\Entity\ProductTranslation")
 */
class ProductAttributeValue extends AbstractEntityAttributeValue
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
