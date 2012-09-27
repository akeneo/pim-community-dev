<?php
namespace Strixos\IcecatConnectorBundle\Entity;

use Strixos\CoreBundle\Model\AbstractModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="StrixosIcecatConnector_Supplier")
 * @ORM\Entity
 */
class Supplier extends AbstractModel
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
     * @var string $supplierId
     *
     * @ORM\Column(name="supplier_id", type="integer")
     */
    private $supplierId;

    /**
     * @var string $supplierName
     *
     * @ORM\Column(name="supplier_name", type="string", length=255)
     */
    private $supplierName;

    /**
     * @var string $distributorId
     *
     * @ORM\Column(name="distributor_id", type="integer")
     */
    private $distributorId;

    /**
     * @var string $symbol
     *
     * @ORM\Column(name="symbol", type="string", length=255)
     */
    private $symbol;

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
     * Set supplierId
     *
     * @param integer $supplierId
     * @return Supplier
     */
    public function setSupplierId($supplierId)
    {
        $this->supplierId = $supplierId;

        return $this;
    }

    /**
     * Get supplierId
     *
     * @return integer
     */
    public function getSupplierId()
    {
        return $this->supplierId;
    }

    /**
     * Set supplierName
     *
     * @param string $supplierName
     * @return Supplier
     */
    public function setSupplierName($supplierName)
    {
        $this->supplierName = $supplierName;

        return $this;
    }

    /**
     * Get supplierName
     *
     * @return string
     */
    public function getSupplierName()
    {
        return $this->supplierName;
    }

    /**
     * Set distributorId
     *
     * @param integer $distributorId
     * @return Supplier
     */
    public function setDistributorId($distributorId)
    {
        $this->distributorId = $distributorId;

        return $this;
    }

    /**
     * Get distributorId
     *
     * @return integer
     */
    public function getDistributorId()
    {
        return $this->distributorId;
    }



    /**
     * Set symbol
     *
     * @param string $symbol
     * @return Supplier
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;
    
        return $this;
    }

    /**
     * Get symbol
     *
     * @return string 
     */
    public function getSymbol()
    {
        return $this->symbol;
    }
}