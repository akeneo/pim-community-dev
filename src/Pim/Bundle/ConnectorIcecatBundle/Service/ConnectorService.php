<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ODM\MongoDB\DocumentManager;

use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;
use Pim\Bundle\ConnectorIcecatBundle\Entity\ConfigManager;
use Pim\Bundle\ConnectorIcecatBundle\Entity\SourceSupplier;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpReader;
use Pim\Bundle\ConnectorIcecatBundle\Extract\ProductXmlExtractor;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Read\SuppliersXmlFromUrl;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Read\ProductSetXmlFromUrl;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Read\DownloadAndUnpackFromUrl;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\ProductSetXmlToDataSheetTransformer;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\ProductValuesXmlToDataSheetTransformer;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\DataSheetArrayToProductTransformer;

use Pim\Bundle\ConnectorIcecatBundle\Transform\LanguagesTransform;
use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductsTransform;
use Pim\Bundle\ConnectorIcecatBundle\Transform\SuppliersTransform;
use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductXmlToArrayTransformer;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\AttributesFromDataSheetsWriter;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\BaseIcecatProductsFromCsvWriter;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\DetailledIcecatProductsFromUrlWriter;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\SuppliersFromXmlWriter;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\LanguagesFromXmlWriter;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\ProductsFromDataSheetsWriter;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\SetsFromDataSheetsWriter;

use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;

/**
 * Connector service, accessibble from anywhere in application
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ConnectorService
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * Config manager
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * Constructor
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->configManager = $this->container->get('pim.connector_icecat.config_manager');
    }

    /**
     * @return DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->container->get('doctrine.odm.mongodb.document_manager');
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * Import suppliers from icecat database
     */
    public function importIcecatSuppliers()
    {
        // Get config
        $login    = $this->configManager->getValue(Config::LOGIN);
        $password = $this->configManager->getValue(Config::PASSWORD);
        $baseDir  = $this->configManager->getValue(Config::BASE_DIR);
        $url      = $this->configManager->getValue(Config::SUPPLIERS_URL);
        $filePath    = $baseDir . $this->configManager->getValue(Config::SUPPLIERS_FILE);
        $forceDownloadFile = true;

        // Extract
        $extractor = new SuppliersXmlFromUrl($url, $login, $password);
        $extractor->extract();
        $xmlContent = $extractor->getXmlContent();

        // Import
        $writer = new SuppliersFromXmlWriter();
        $writer->import($xmlContent, $this->getEntityManager());
    }

    /**
     * Import languages from icecat database
     */
    public function importIcecatLanguages()
    {
        // Get config
        $login    = $this->configManager->getValue(Config::LOGIN);
        $password = $this->configManager->getValue(Config::PASSWORD);
        $baseDir  = $this->configManager->getValue(Config::BASE_DIR);
        $url      = $this->configManager->getValue(Config::LANGUAGES_URL);
        $filePath = $baseDir . $this->configManager->getValue(Config::LANGUAGES_FILE);
        $archivePath = $baseDir . $this->configManager->getValue(Config::LANGUAGES_ARCHIVED_FILE);
        $forceDownloadFile = true;

        // Extractor
        $extractor = new DownloadAndUnpackFromUrl($url, $login, $password, $archivePath, $filePath, $forceDownloadFile);
        $extractor->extract();
        $xmlContent = file_get_contents($filePath);

        // Import
        $writer= new LanguagesFromXmlWriter();
        $writer->import($xmlContent, $this->getEntityManager());
    }

    /**
     * Import base products from icecat database
     */
    public function importIcecatBaseProducts()
    {
        // Get config
        $login       = $this->configManager->getValue(Config::LOGIN);
        $password    = $this->configManager->getValue(Config::PASSWORD);
        $baseDir     = $this->configManager->getValue(Config::BASE_DIR);
        $url         = $this->configManager->getValue(Config::PRODUCTS_URL);
        $filePath    = $baseDir . $this->configManager->getValue(Config::PRODUCTS_FILE);
        $archivePath = $baseDir . $this->configManager->getValue(Config::PRODUCTS_ARCHIVED_FILE);
        $forceDLFile = false;

        // 1. get data
        TimeHelper::addValue('download-file');
        $extractor = new DownloadAndUnpackFromUrl($url, $login, $password, $archivePath, $filePath, $forceDLFile);
        $extractor->extract();
        echo 'Download File -> '. TimeHelper::writeGap('download-file').PHP_EOL;

        // 2. import base products
        TimeHelper::addValue('import-base-product');
        $writer = new BaseIcecatProductsFromCsvWriter();
        $writer->import($filePath, $this->getDocumentManager());
        echo 'Insert base product -> '. TimeHelper::writeGap('import-base-product');
    }

    /**
     * Import details for base products from icecat database
     *
     * @param integer $limit nb product to import
     */
    public function importIcecatDetailledProducts($limit)
    {
        // Get config
        $login       = $this->configManager->getValue(Config::LOGIN);
        $password    = $this->configManager->getValue(Config::PASSWORD);
        $baseUrl     = $this->configManager->getValue(Config::BASE_URL);
        $baseProdUrl = $baseUrl.$this->configManager->getValue(Config::BASE_PRODUCTS_URL);

        // import detailled products
        TimeHelper::addValue('import-detailled-product');
        $writer = new DetailledIcecatProductsFromUrlWriter();
        $writer->import($this->getDocumentManager(), $baseProdUrl, $login, $password, $limit);
        echo 'Insert detailled product -> '. TimeHelper::writeGap('import-detailled-product');
    }

    /**
     * Import products from icecat datasheet
     *
     * @param integer $limit nb product to import
     *
     * TODO : Set a limit when recovering datasheet
     * TODO : Request only IcecatProductDataSheet where status = 2
     */
    public function importProductsFromDataSheet($limit)
    {
        // TODO : must be removed. just for not flush values
        $flush = true;
        echo "import products from data sheet...\n";

        $productManager = $this->container->get('pim.catalog.product_manager');
        $docManager     = $this->getDocumentManager();
        $dataSheets     = $docManager->getRepository('PimConnectorIcecatBundle:IcecatProductDataSheet')->findAll();

        // TODO : Hook to get limited data sheet.. Must be removed.
        $limitedDataSheets = array();
        foreach ($dataSheets as $dataSheet) {
            $limitedDataSheets[] = $dataSheet;
            if (--$limit === 0) {
                break;
            }
        }

        // call writers
        $attributeWriter = new AttributesFromDataSheetsWriter();
        $attributeWriter->import($productManager, $limitedDataSheets, $flush);

        $productWriter = new ProductsFromDataSheetsWriter();
        $productsError = $productWriter->import($productManager, $limitedDataSheets, $flush);

        // update IcecatProductDataSheet status
        foreach ($limitedDataSheets as $dataSheet) {
            $dataSheet->setStatus(IcecatProductDataSheet::STATUS_FINISHED);
            $docManager->persist($dataSheet);
        }

        // update IcecatProductDataSheet error status
        foreach ($productsError as $dataSheetId => $message) {
            $dataSheet = $docManager->getRepository('PimConnectorIcecatBundle:IcecatProductDataSheet')->find($dataSheetId);
            $dataSheet->setStatus(IcecatProductDataSheet::STATUS_ERROR);
            $docManager->persist($dataSheet);
        }

        if ($flush) {
            $productManager->getPersistenceManager()->flush();
        }
    }

    /**
     * TODO: refactor
     * Import a product by its icecat id
     *
     * @param string  $datasheetId datasheet id
     * @param boolean $toFlush     true to flush
     */
    public function importProductFromIcecatXml($datasheetId, $toFlush = false)
    {
        // get conf and product url
        $login       = $this->configManager->getValue(Config::LOGIN);
        $password    = $this->configManager->getValue(Config::PASSWORD);
        $baseUrl     = $this->configManager->getValue(Config::BASE_URL);
        $baseProdUrl = $this->configManager->getValue(Config::BASE_PRODUCTS_URL);

        // 1. get related datasheet
        $docManager = $this->container->get('doctrine.odm.mongodb.document_manager');
        $datasheet = $docManager->getRepository('PimConnectorIcecatBundle:IcecatProductDataSheet')->find($datasheetId);
        $datasheetUrl = $baseUrl.$baseProdUrl.$datasheet->getProductId().'.xml';

        // retrieve details if not already done
        if (!$datasheet->isImported()) {

            // 2. extract product xml from icecat
            $reader = new ProductSetXmlFromUrl($datasheetUrl, $login, $password);
            $reader->extract();
            $simpleXml = simplexml_load_string($reader->getXmlContent());

            // 3 A. enrich datasheet with set data
            $transformer = new ProductSetXmlToDataSheetTransformer($simpleXml, $datasheet);
            $transformer->enrich();

            // 3 B. enrich datasheet with product data
            $transformer = new ProductValuesXmlToDataSheetTransformer($simpleXml, $datasheet, 1);// TODO force english locale
            $transformer->enrich();

            // 4. flush details
            $docManager->flush(); // TODO : toflush param only for product manager
        }

        // 5. create / update attributes, set, product
        $productManager = $this->container->get('pim.catalog.product_manager');
        $writer = new ProductsFromDataSheetsWriter();
        $writer->import($productManager, $datasheet, $toFlush);

        // 6. flush if not in batch mode
        if ($toFlush) {
            $productManager->getPersistenceManager()->flush();
        }
    }

    /**
     *      * TODO: refactor
     * Import all products from a supplier
     * @param SourceSupplier $supplier
     */
    public function importProductsFromSupplier(SourceSupplier $supplier)
    {
        $products = $this->getEntityManager()
                         ->getRepository('PimConnectorIcecatBundle:SourceProduct')
                         ->findBySupplier($supplier);

        foreach ($products as $product) {
            $this->importProductFromIcecatXml($product->getId());
        }
    }
}
