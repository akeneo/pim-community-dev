<?php
namespace Strixos\IcecatConnectorBundle\Transform;

use Strixos\IcecatConnectorBundle\Entity\Product;

use Strixos\IcecatConnectorBundle\Load\EntityLoad;
use \XMLReader;
use Strixos\IcecatConnectorBundle\Model\Service\ProductsService;

/**
 * Aims to transform suppliers xml file to csv file
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : MAKE interfaces to implements xml to csv, xml to php, csv to php, etc.
 */
class ProductsTransform extends IcecatTransform
{
	
	protected $loader;
	
	
	
	/**
     * Constructor
     * @param SupplierLoader $loader
     */
    public function __construct($loader)
    {
        //$this->container = $container;
        $this->loader = $loader;
    }
	
    /**
     * Transform xml file to csv
     *
     * @param string $xmlFile
     * @param string $csvFile
     */
    public function process()
    {
    // truncate base suppliers
        $connection = $this->entityManager->getConnection();
        $platform   = $connection->getDatabasePlatform();
        $tableName = 'StrixosIcecatConnector_Product';
        $connection->executeUpdate($platform->getTruncateTableSQL($tableName));
        // load suppliers
        // TODO: use custom repository to get associative array in more efficient way
        $suppliers = $this->entityManager->getRepository('StrixosIcecatConnectorBundle:Supplier')->findAll();
        $this->_icecatIdToSupplier = array();
        foreach ($suppliers as $supplier) {
            $this->_icecatIdToSupplier[$supplier->getIcecatId()] = $supplier;
        }
        // import products
        if (($handle = fopen($csvFile, 'r')) !== false) {
            $length = 1000;
            $delimiter = "\t";
            $indRow = 0;
            while (($data = fgetcsv($handle, $length, $delimiter)) !== false) {
                // not parse header
                if ($indRow++ == 0) {
                    continue;
                }
                // inject as product
                $product = new Product();
                $product->setProductId($data[0]);
                // TODO: get real supplier id problem with mapping
                $product->setSupplier($this->_icecatIdToSupplier[$data[4]]);
                $product->setProdId($data[1]);
                $product->setMProdId($data[10]);
                $this->loader->add($product);
            }
            $this->loader->flush();
        }
    }
}
