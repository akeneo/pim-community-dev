<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Service;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\InsertLanguagesFromXml;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\InsertSuppliersFromXml;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpReader;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\InsertBaseIcecatProductsFromCsv;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\InsertDetailledIcecatProductsFromUrl;

use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;
use Pim\Bundle\ConnectorIcecatBundle\Entity\ConfigManager;
use Pim\Bundle\ConnectorIcecatBundle\Entity\SourceSupplier;
use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;

use Pim\Bundle\ConnectorIcecatBundle\Extract\ProductXmlExtractor;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Read\SuppliersXmlFromUrl;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Read\ProductSetXmlFromUrl;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Read\DownloadAndUnpackFromUrl;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\ProductSetXmlToDataSheetTransformer;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\ProductValuesXmlToDataSheetTransformer;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\DataSheetArrayToProductTransformer;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\InsertProductFromDataSheet;

use Pim\Bundle\ConnectorIcecatBundle\Transform\LanguagesTransform;
use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductsTransform;
use Pim\Bundle\ConnectorIcecatBundle\Transform\SuppliersTransform;
use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductXmlToArrayTransformer;
use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductArrayToCatalogProductTransformer;

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
        $this->configManager = new ConfigManager($this->container->get('doctrine.orm.entity_manager'));
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
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $writer = new InsertSuppliersFromXml();
        $writer->import($xmlContent, $entityManager);
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
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $writer= new InsertLanguagesFromXml();
        $writer->import($xmlContent, $entityManager);
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
        echo /*$this->writeln(*/ 'Download File -> '. TimeHelper::writeGap('download-file').PHP_EOL; //);

        // 2. import base products
        TimeHelper::addValue('import-base-product');
        $manager = $this->container->get('doctrine.odm.mongodb.document_manager');
        $writer = new InsertBaseIcecatProductsFromCsv();
        $writer->import($filePath, $manager);
        echo /*$this->writeln(*/'Insert base product -> '. TimeHelper::writeGap('import-base-product')/*);*/;
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
        $manager = $this->container->get('doctrine.odm.mongodb.document_manager');
        $writer = new InsertDetailledIcecatProductsFromUrl();
        $writer->import($manager, $baseProdUrl, $login, $password, $limit);
        echo /*$this->writeln(*/'Insert base product -> '. TimeHelper::writeGap('import-detailled-product')/*);*/;
    }

    /**
     * Import products from icecat datasheet
     *
     * @param integer $limit nb product to import
     */
    public function importProductsFromDataSheet($limit)
    {
        echo "import products from data sheet...";
        $docManager = $this->container->get('doctrine.odm.mongodb.document_manager');
        $datasheets = $docManager->getRepository('PimConnectorIcecatBundle:IcecatProductDataSheet')->findAll();

        foreach ($datasheets as $datasheet) {
            $productManager = $this->container->get('pim.catalog.product_manager');
            $writer = new InsertProductFromDataSheet();
            $writer->import($productManager, $datasheet, false);

            if (--$limit === 0) {
                break;
            }
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
        $writer = new InsertProductFromDataSheet();
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
        $em = $this->container->get('doctrine.orm.entity_manager');
        $products = $em->getRepository('PimConnectorIcecatBundle:SourceProduct')->findBySupplier($supplier);

        foreach ($products as $product) {
            $this->importProductFromIcecatXml($product->getId());
        }
    }
}
