<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations\Boolean;

use Doctrine\ODM\MongoDB\Mapping\Annotations\String;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
/**
 *
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @MongoDB\Document
 */
class ProductDataSheetDocument
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
     * Product detailed remote import path
     * @var string
     *
     * @MongoDB\String
     */
    protected $importPath;

    /**
     * Whole line for basic product data
     * @var string
     *
     * @MongoDB\String
     */
    protected $xmlBaseData;

    /**
     * Detailed data for product
     * @var string
     *
     * @MongoDB\String
     */
    protected $xmlDetailledData;

    /**
     * Detailled data is imported
     * @var Boolean
     *
     * @MongoDB\Boolean
     */
    protected $isImported;

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
     * @return ProductDataSheetDocument
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
     * Set importPath
     *
     * @param  string                   $importPath
     * @return ProductDataSheetDocument
     */
    public function setImportPath($importPath)
    {
        $this->importPath = $importPath;

        return $this;
    }

    /**
     * Get importPath
     *
     * @return string $importPath
     */
    public function getImportPath()
    {
        return $this->importPath;
    }

    /**
     * Set xmlBaseData
     *
     * @param  string                   $xmlBaseData
     * @return ProductDataSheetDocument
     */
    public function setXmlBaseData($xmlBaseData)
    {
        $this->xmlBaseData = $xmlBaseData;

        return $this;
    }

    /**
     * Get xmlBaseData
     *
     * @return string $xmlBaseData
     */
    public function getXmlBaseData()
    {
        return $this->xmlBaseData;
    }

    /**
     * Set xmlDetailledData
     *
     * @param  string                   $xmlDetailledData
     * @return ProductDataSheetDocument
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

    /**
     * Set isImported
     *
     * @param  boolean                  $isImported
     * @return ProductDataSheetDocument
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
}
