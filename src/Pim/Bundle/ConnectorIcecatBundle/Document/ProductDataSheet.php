<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations\Boolean;

use Doctrine\ODM\MongoDB\Mapping\Annotations\String;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
/**
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @MongoDB\Document
 */
class ProductDataSheet
{
    /**
     * @var integer
     *
     * @MongoDB\Id
     */
    protected $id;

    /**
     * Reference to icecat product id
     * @var integer
     *
     * @MongoDB\Int
     * @MongoDB\Index(unique=true)
     */
    protected $productId;

    /**
     * Detailled data is imported
     * @var boolean
     *
     * @MongoDB\Boolean
     */
    protected $isImported;

    /**
     * Detailed data for product
     * @var string
     *
     * @MongoDB\String
     */
    protected $xmlDetailledData;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->isImported = false;
    }

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
     * @param  int                      $productId
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
     * Set isImported
     *
     * @param  boolean                  $isImported
     * @return ProductDataSheet
     */
    public function setIsImported($isImported)
    {
        $this->isImported = $isImported;

        return $this;
    }

    /**
     * Get isImported
     *
     * @return boolean $isImported
     */
    public function getIsImported()
    {
        return $this->isImported;
    }

    /**
     * Set xmlDetailledData
     *
     * @param  string                   $xmlDetailledData
     * @return ProductDataSheet
     */
    public function setXmlDetailledData($xmlDetailledData)
    {
        $this->xmlDetailledData = $xmlDetailledData;

        return $this;
    }

    /**
     * Get xmlDetailledData
     *
     * @return string $xmlDetailledData
     */
    public function getXmlDetailledData()
    {
        return $this->xmlDetailledData;
    }
}
