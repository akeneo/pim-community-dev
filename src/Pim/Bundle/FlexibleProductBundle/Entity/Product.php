<?php
namespace Pim\Bundle\FlexibleProductBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Doctrine\ORM\Mapping as ORM;

/**
 * Flexible product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="flexibleproduct_product")
 * @ORM\Entity(repositoryClass="Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository")
 */
class Product extends AbstractEntityFlexible
{
    /**
     * @var string $sku
     *
     * @ORM\Column(name="sku", type="string", length=255, unique=true)
     */
    protected $sku;

    /**
     * @var Value
     *
     * @ORM\OneToMany(targetEntity="ProductAttributeValue", mappedBy="entity", cascade={"persist", "remove"})
     */
    protected $values;

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Set sku
     *
     * @param string $sku
     *
     * @return EntityAttribute
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

}
