<?php
namespace Oro\Bundle\ProductBundle\Entity;

use Oro\Bundle\DataModelBundle\Entity\EntityAttribute as AbstractEntityAttribute;
use Oro\Bundle\DataModelBundle\Model\EntityAttributeOption as AbstractEntityAttributeOption;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Product attribute as sku, name, etc
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="product_attribute", indexes={@ORM\Index(name="searchcode_idx", columns={"code"})} )
 * @ORM\Entity()
 * @Gedmo\TranslationEntity(class="Oro\Bundle\ProductBundle\Entity\ProductTranslation")
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
