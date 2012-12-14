<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Icecat product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="akeneo_connectoricecat_sourceproduct")
 * @ORM\Entity
 */
class SourceProduct
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
     * @var Supplier $supplier
     * @ORM\ManyToOne(targetEntity="SourceSupplier")
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    protected $supplier;

    /**
     * @var integer $productId
     *
     * @ORM\Column(name="product_id", type="integer")
     */
    protected $productId;

    /**
     * @var string $prodId
     *
     * @ORM\Column(name="prod_id", type="string", length=255)
     */
    protected $prodId;

    /**
     * @var string $mProdId
     *
     * @ORM\Column(name="m_prod_id", type="string", length=255)
     */
    protected $mProdId;

    /**
     * @var boolean $isImported
     *
     * @ORM\Column(name="is_imported", type="boolean")
     */
    protected $isImported;

    /**
     * Constructor for product
     * Define default value for is_imported attribute
     */
    public function __construct()
    {
        $this->isImported = false;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set productId
     * @param integer $productId
     *
     * @return Product
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get productId
     *
     * @return integer
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set prodId
     * @param string $prodId
     *
     * @return Product
     */
    public function setProdId($prodId)
    {
        $this->prodId = $prodId;

        return $this;
    }

    /**
     * Get prodId
     *
     * @return string
     */
    public function getProdId()
    {
        return $this->prodId;
    }

    /**
     * Set mProdId
     * @param string $mProdId
     *
     * @return Product
     */
    public function setMProdId($mProdId)
    {
        $this->mProdId = $mProdId;

        return $this;
    }

    /**
     * Get mProdId
     *
     * @return string
     */
    public function getMProdId()
    {
        return $this->mProdId;
    }

    /**
     * Set supplier
     * @param Pim\Bundle\ConnectorIcecatBundle\Entity\SourceSupplier $supplier
     *
     * @return Product
     */
    public function setSupplier(\Pim\Bundle\ConnectorIcecatBundle\Entity\SourceSupplier $supplier = null)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * Get supplier
     *
     * @return Pim\Bundle\ConnectorIcecatBundle\Entity\SourceSupplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * Set is_imported
     * @param bool $isImported
     *
     * @return Product
     */
    public function setIsImported($isImported)
    {
        $this->isImported = $isImported;

        return $this;
    }

    /**
     * Get is_imported
     *
     * @return bool
     */
    public function getIsImported()
    {
        return $this->isImported;
    }
}
