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
     * @var string $productId
     *
     * @ORM\Column(name="product_id", type="string", length=255)
     */
    private $productId;

    /**
     * @var string $productName
     *
     * @ORM\Column(name="product_name", type="string", length=255)
     */
    private $productName;

    /**
     * TODO: add fk constraint
     * @var string $supplierId
     *
     * @ORM\Column(name="supplier_id", type="string", length=255)
     */
    private $supplierId;

/* TODO store update date and xml content
    private $updatedBase;
    private $updatedDetails;
    xml content
*/




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
     * Set productName
     *
     * @param string $productName
     * @return Product
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;
    
        return $this;
    }

    /**
     * Get productName
     *
     * @return string 
     */
    public function getProductName()
    {
        return $this->productName;
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
}