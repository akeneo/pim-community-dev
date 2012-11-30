<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Service;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpReader;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\InsertBaseIcecatProductsFromCsv;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\InsertDetailledIcecatProductsFromUrl;

use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;
use Pim\Bundle\ConnectorIcecatBundle\Entity\ConfigManager;
use Pim\Bundle\ConnectorIcecatBundle\Entity\SourceSupplier;

use Pim\Bundle\ConnectorIcecatBundle\Extract\ProductXmlExtractor;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Read\SuppliersXmlUrl;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Read\ProductXmlUrl;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Read\DownloadAndUnpackFromUrl;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\ProductIntXmlToArrayTransformer;

use Pim\Bundle\ConnectorIcecatBundle\Transform\LanguagesTransform;
use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductsTransform;
use Pim\Bundle\ConnectorIcecatBundle\Transform\SuppliersTransform;
use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductXmlToArrayTransformer;
use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductArrayToCatalogProductTransformer;

use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;

use Pim\Bundle\ConnectorIcecatBundle\Load\BatchLoader;
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

        // Call extractor
        $extractor = new SuppliersXmlUrl($url, $login, $password);
        $extractor->extract();
        $xmlContent = $extractor->getXmlContent();

        $transformer = new SuppliersTransform($this->container->get('doctrine.orm.entity_manager'), $xmlContent);
        $transformer->transform();
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

        // Call extractor
        $extractor = new DownloadAndUnpackFromUrl($url, $login, $password, $archivePath, $filePath, $forceDownloadFile);
        $extractor->extract();
        $xmlContent = $extractor->getReadContent();

        $loader = new BatchLoader($this->container->get('doctrine.orm.entity_manager'));
        $transformer = new LanguagesTransform($loader, $xmlContent);
        $transformer->transform();
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

        // get related datasheet
        $docManager = $this->container->get('doctrine.odm.mongodb.document_manager');
        $datasheet = $docManager->getRepository('PimConnectorIcecatBundle:IcecatProductDataSheet')->find($datasheetId);
        $datasheetUrl = $baseUrl.$baseProdUrl.$datasheet->getProductId().'.xml';

        // TODO depends on status !

        // 2. extract product xml from icecat
        $reader = new ProductXmlUrl($datasheetUrl, $login, $password);
        $reader->extract();
        $simpleXml = $reader->getXmlContent();

        // 3. transform product xml to lines (associative array)
        $transformer = new ProductIntXmlToArrayTransformer($simpleXml);
        $productData = $transformer->transform();

        // 4. set

        // TODO 4. add other lang !


        /*
        // 5. transform array to pim product
        $productManager = $this->container->get('pim.catalog.product_manager');
        $transformer = new ProductArrayToCatalogProductTransformer($productManager, $productBaseData, $productFeatures, $pimLocale);
        $transformer->transform();
*/
/*

        // 6. Update icecat product imported
        $baseProduct->setIsImported(true);
        $em->persist($baseProduct);
        $em->flush();
*/

/*
        if ($datasheet->isImported()) {
            // transform

            // insert
        }
*/
        /*
        // TODO by configuration, for now en_US first is important
        $localeIceToPim = array('US' => 'en_US'/); // no translate for now

        // 1. get base product from icecat referential
        $em = $this->container->get('doctrine.orm.entity_manager');
        $baseProduct = $em->getRepository('PimConnectorIcecatBundle:SourceProduct')->find($productId);
        $prodId = $baseProduct->getProdId();
        $supplierName = $baseProduct->getSupplier()->getName();

        foreach ($localeIceToPim as $icecatLocale => $pimLocale) {

            // 2. extract product xml from icecat
            $extractor = new ProductXmlExtractor($prodId, $supplierName, $icecatLocale, $this->configManager);
            $extractor->extract();
            $simpleXml = $extractor->getReadContent();

            // 3. transform product xml to lines (associative array)
            $transformer = new ProductXmlToArrayTransformer($simpleXml);
            $transformer->transform();
            $productBaseData = $transformer->getProductBaseData();
            $productFeatures = $transformer->getProductFeatures();

            // 4. transform array to pim product
            $productManager = $this->container->get('pim.catalog.product_manager');
            $transformer = new ProductArrayToCatalogProductTransformer($productManager, $productBaseData, $productFeatures, $pimLocale);
            $transformer->transform();

        }
*/
        // 6. Update icecat product imported
 //       $baseProduct->setIsImported(true);
        $em->persist($baseProduct);
        if ($toFlush) {
            $em->flush();
        }
    }

    /**
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
