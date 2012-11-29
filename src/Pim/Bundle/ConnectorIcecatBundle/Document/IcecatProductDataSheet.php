<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations\Boolean;

use Doctrine\ODM\MongoDB\Mapping\Annotations\String;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use APY\DataGridBundle\Grid\Mapping as GRID;
/**
 * Icecat product datasheet
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @MongoDB\Document
 * @GRID\Source(columns="id, productId, supplierId, status")
 */
class IcecatProductDataSheet
{
    const STATUS_INIT   = 1;
    const STATUS_IMPORT = 2;
    const STATUS_ERROR  = 3;

    /**
     * @var integer
     *
     * @MongoDB\Id
     * @GRID\Column()
     */
    protected $id;

    /**
     * Reference to icecat product id
     * @var integer
     *
     * @MongoDB\Int
     * @GRID\Column()
     */
    protected $productId;

    /**
     * Reference to icecat supplier id
     * @var integer
     *
     * @MongoDB\Int
     * @GRID\Column()
     */
    protected $supplierId;

    /**
     * Detailled data is imported
     * @var integer
     *
     * @MongoDB\Int
     * @GRID\Column()
     */
    protected $status;

    /**
     * Detailed data for product
     * @var string
     *
     * @MongoDB\String
     */
    protected $data;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set productId
     *
     * @param int $productId
     *
     * @return ProductDataSheet
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get productId
     *
     * @return int $productId
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set supplier id
     *
     * @param integer $supplierId
     *
     * @return ProductDataSheet
     */
    public function setSupplierId($supplierId)
    {
        $this->supplierId = $supplierId;

        return $this;
    }

    /**
     * Get supplier id
     *
     * @return integer
     */
    public function getSupplierId()
    {
        return $this->supplierId;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return ProductDataSheet
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return ProductDataSheet
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string $data
     */
    public function getData()
    {
        return $this->data;
    }
}
