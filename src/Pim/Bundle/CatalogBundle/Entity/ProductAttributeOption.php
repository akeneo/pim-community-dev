<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityAttributeOption as AbstractEntityAttributeOption;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * Field options
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="Akeneo_PimCatalog_Product_AttributeOption")
 * @ORM\Entity
 */
class ProductAttributeOption extends AbstractEntityAttributeOption
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Field $attribute
     *
     * @ORM\ManyToOne(targetEntity="ProductAttribute")
     */
    protected $attribute;

    /**
     * @ORM\Column(name="data", type="string", length=255)
     */
    protected $value;

    /**
     * @ORM\Column(name="sort_order", type="integer")
     */
    protected $sortOrder;

    /**
     * Set attribute
     *
     * @param ProductAttribute $attribute
     * @return ProductAttributeOption
     */
    public function setAttribute(ProductAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }
}