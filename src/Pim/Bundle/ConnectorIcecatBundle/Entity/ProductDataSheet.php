<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Icecat product data sheet
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="Akeneo_PimConnectorIcecat_ProductDataSheet")
 * @ORM\Entity
 */
class ProductDataSheet
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Reference to icecat product id
     * @var integer
     *
     * @ORM\Column(name="product_id", type="integer", unique=true)
     */
    protected $productId;

    /**
     * Detailled data is imported
     * @var integer
     *
     * @ORM\Column(name="is_imported", type="boolean")
     */
    protected $isImported;

    /**
     * Detailed data for product
     * @var string
     *
     * @ORM\Column(name="xml_detailled_data", type="text", nullable=true)
     */
    protected $xmlDetailledData;

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
     * Set isImported
     *
     * @param integer $isImported
     *
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
     * @return integer $isImported
     */
    public function getIsImported()
    {
        return $this->isImported;
    }

    /**
     * Set xmlDetailledData
     *
     * @param string $xmlDetailledData
     *
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
