<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\EntityAttribute as AbstractEntityAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\EntityAttributeOption as AbstractEntityAttributeOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * Product attribute as sku, name, etc
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="akeneo_catalog_product_attribute")
 * @ORM\Entity(repositoryClass="Pim\Bundle\CatalogBundle\Entity\ProductAttributeRepository")
 */
class ProductAttribute extends AbstractEntityAttribute
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
