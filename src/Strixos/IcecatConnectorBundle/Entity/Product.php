<?php
namespace Strixos\IcecatConnectorBundle\Entity;

use Strixos\CoreBundle\Model\AbstractModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="StrixosIcecatConnector_Product")
 * @ORM\Entity
 */
class Product extends AbstractModel
{

   /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Supplier $supplier
     * @ORM\ManyToOne(targetEntity="Supplier")
     */
    private $supplier;

    /**
     * @var integer $productId
     *
     * @ORM\Column(name="product_id", type="integer")
     */
    private $productId;


    /**
     * @var string $prodId
     *
     * @ORM\Column(name="prod_id", type="string", length=255)
     */
    private $prodId;


    /**
     * @var string $mProdId
     *
     * @ORM\Column(name="m_prod_id", type="string", length=255)
     */
    private $mProdId;

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
     *
     * @param string $productId
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
     * @return string 
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set supplierId
     *
     * @param string $supplierId
     * @return Product
     */
    public function setSupplierId($supplierId)
    {
        $this->supplierId = $supplierId;
    
        return $this;
    }

    /**
     * Get supplierId
     *
     * @return string 
     */
    public function getSupplierId()
    {
        return $this->supplierId;
    }

    /**
     * Set supplier
     *
     * @param Strixos\IcecatConnectorBundle\Entity\Supplier $supplier
     * @return Product
     */
    public function setSupplier(\Strixos\IcecatConnectorBundle\Entity\Supplier $supplier = null)
    {
        $this->supplier = $supplier;
    
        return $this;
    }

    /**
     * Get supplier
     *
     * @return Strixos\IcecatConnectorBundle\Entity\Supplier 
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * Set prodId
     *
     * @param string $prodId
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
     *
     * @param string $mProdId
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
}