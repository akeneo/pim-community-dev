<?php
namespace Pim\Bundle\ProductBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Doctrine\ORM\Mapping as ORM;

/**
 * Flexible product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_product")
 * @ORM\Entity(repositoryClass="Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository")
 * @UniqueEntity("sku");
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
     * @ORM\OneToMany(targetEntity="ProductValue", mappedBy="entity", cascade={"persist", "remove"})
     */
    protected $values;

    /**
     * @var productFamily
     *
     * @ORM\ManyToOne(targetEntity="ProductFamily")
     */
    protected $productFamily;

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

    /**
     * Get filepath
     *
     * @return string
     */
    public function getFilepath()
    {
        return $this->file;
    }

    /**
     * Set filepath
     *
     * @param string $pictureName
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function setFilepath($pictureName)
    {
        $this->filepath = $pictureName;

        return $this;
    }

    /**
     * Get product family
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductFamily
     */
    public function getProductFamily()
    {
        return $this->productFamily;
    }

    /**
     * Set product family
     *
     * @param ProductFamily $productFamily
     *
     * @return EntityAttribute
     */
    public function setProductFamily($productFamily)
    {
        $this->productFamily = $productFamily;

        return $this;
    }
}
