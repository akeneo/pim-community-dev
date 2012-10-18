<?php
namespace Strixos\IcecatConnectorBundle\Service;

use Strixos\IcecatConnectorBundle\Entity\Config;

use Strixos\IcecatConnectorBundle\Extract\ProductXmlExtractor;
use Strixos\IcecatConnectorBundle\Transform\ProductXmlToArrayTransformer;
use Strixos\IcecatConnectorBundle\Transform\ProductArrayToCatalogProductTransformer;

use Strixos\IcecatConnectorBundle\Extract\DownloadAndUnpackSource;

use Strixos\IcecatConnectorBundle\Transform\LanguagesTransform;
use Strixos\IcecatConnectorBundle\Transform\ProductTransform;
use Strixos\IcecatConnectorBundle\Transform\ProductsTransform;
use Strixos\IcecatConnectorBundle\Transform\SuppliersTransform;

use Strixos\IcecatConnectorBundle\Load\BatchLoader;

/**
 * Connector service, accessibble from anywhere in application
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ConnectorService
{

    protected $container;

    /**
     * Constructor
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function importSuppliers()
    {
        // TODO get from config
        $url = 'http://data.icecat.biz/export/freeurls/supplier_mapping.xml';
        $login = 'NicolasDupont';
        $password = '1cec4t**)';
        $archivePath = '/tmp/suppliers-list.xml.gz';
        $filePath = '/tmp/suppliers-list.xml';
        $forceDownloadFile = false;
        $extractor = new DownloadAndUnpackSource($url, $login, $password, $archivePath, $filePath, $forceDownloadFile);
        $extractor->extract();

        $loader = new BatchLoader($this->container->get('doctrine.orm.entity_manager'));
        $transformer = new SuppliersTransform($loader);
        $transformer->transform();
    }

    public function importLanguages()
    {
        $em   = $this->container->get('doctrine.orm.entity_manager');
        $url  = $em->getRepository('StrixosIcecatConnectorBundle:Config')->findOneByCode(Config::LANGUAGES_URL)->getValue();
        $file = $em->getRepository('StrixosIcecatConnectorBundle:Config')->findOneByCode(Config::LANGUAGES_FILE)->getValue();
        $filePath = '/tmp/'.$file;
        $archivePath = $file .'.gz';
        // TODO get from config
        $login = 'NicolasDupont';
        $password = '1cec4t**)';
        $forceDownloadFile = false;

        $extractor = new DownloadAndUnpackSource($url, $login, $password, $archivePath, $filePath, $forceDownloadFile);
        $extractor->extract();

        $loader = new BatchLoader($this->container->get('doctrine.orm.entity_manager'));
        $transformer = new LanguagesTransform($loader);
        $transformer->transform();
    }

    public function importProducts()
    {
        // TODO get from config
        $url = 'http://data.icecat.biz/export/freeurls/export_urls_rich.txt.gz';
        $login = 'NicolasDupont';
        $password = '1cec4t**)';
        $archivePath = '/tmp/export_urls_rich.txt.gz';
        $filePath = '/tmp/export_urls_rich.txt';
        $forceDownloadFile = false;
        $extractor = new DownloadAndUnpackSource($url, $login, $password, $archivePath, $filePath, $forceDownloadFile);
        $extractor->extract();

        $transformer = new ProductsTransform($this->container->get('doctrine.orm.entity_manager'));
        $transformer->transform();
    }

    /**
     * Import a product by its icecat id
     * @param string $productId
     */
    public function importProductFromIcecatXml($productId)
    {
        // TODO by configuration, for now en_US first is important
        $localeIceToPim = array('US' => 'en_US', 'FR' => 'fr_FR');

        foreach ($localeIceToPim as $icecatLocale => $pimLocale) {

            // 1. get base product from icecat referential
            $em = $this->container->get('doctrine.orm.entity_manager');
            $baseProduct = $em->getRepository('StrixosIcecatConnectorBundle:SourceProduct')->find($productId);
            $prodId = $baseProduct->getProdId();
            $supplierName = $baseProduct->getSupplier()->getName();

            // 2. extract product xml from icecat
            $extractor = new ProductXmlExtractor($prodId, $supplierName, $icecatLocale);
            $extractor->extract();
            $simpleXml = $extractor->getXmlElement();

            // 3. transform product xml to lines (associative array)
            $transformer = new ProductXmlToArrayTransformer($simpleXml);
            $transformer->transform();
            $productBaseData = $transformer->getProductBaseData();
            $productFeatures = $transformer->getProductFeatures();

            // 4. transform array to pim product
            $productTypeService = $this->container->get('akeneo.catalog.model_producttype');
            $productService = $this->container->get('akeneo.catalog.model_product');
            $transformer = new ProductArrayToCatalogProductTransformer($productTypeService, $productService, $productBaseData, $productFeatures, $pimLocale);
            $transformer->transform();

        // 5. load product (move persist / flush from transform to allow batch using for supplier import)
       /*
        foreach ($locales as $locale) {
            $fp = $extract->process($prodId, $supplierName, $locale);
            $transform->process($fp);
        }*/
        }
    }

    public function importProductsFromSupplier($supplier)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $products = $em->getRepository('StrixosIcecatConnectorBundle:SourceProduct')->findBySupplier($supplier);

        foreach ($products as $product) {
            $this->importProductFromIcecatXml($product->getId());
        }
    }
}
