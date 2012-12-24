<?php
namespace Oro\Bundle\ProductBundle\Entity;

use Oro\Bundle\DataModelBundle\Entity\AbstractOrmEntityAttribute;
use Oro\Bundle\DataModelBundle\Model\AbstractEntityAttributeOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * Product attribute as sku, name, etc
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="product_attribute", indexes={@ORM\Index(name="searchcode_idx", columns={"code"})} )
 * @ORM\Entity()
 */
class ProductAttribute extends AbstractOrmEntityAttribute
{

    /**
     * Overrided to change target entity name
     *
     * @var ArrayCollection $options
     *
     * @ORM\OneToMany(targetEntity="ProductAttributeOption", mappedBy="attribute", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $options;

}
