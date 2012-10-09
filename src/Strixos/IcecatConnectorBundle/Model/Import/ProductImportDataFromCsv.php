<?php
namespace Strixos\IcecatConnectorBundle\Model\Import;

use Strixos\IcecatConnectorBundle\Entity\Product;

/**
 *
 * Aims to populate local referencial of products by loading text file
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductImportDataFromCsv extends DataImport
{
    /**
     * @var Array
     */
    protected $_icecatIdToSupplier;

    /**
     * (non-PHPdoc)
     * @see \Strixos\IcecatConnectorBundle\Model\Import\DataImport::process()
     */
    public function process($csvFile)
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
        $batchSize = 1000;
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
                //$product->setSupplier($this->_icecatIdToSupplier[$data[4]]);
                $product->setProdId($data[1]);
                $product->setMProdId($data[10]);
                $this->entityManager->persist($product);
                // flush and detach
                if (($indRow % $batchSize) == 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear(); // detaches all objects from Doctrine
                }
            }
        }
    }

}
