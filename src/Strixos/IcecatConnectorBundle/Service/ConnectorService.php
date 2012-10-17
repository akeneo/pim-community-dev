<?php
namespace Strixos\IcecatConnectorBundle\Service;

use Strixos\IcecatConnectorBundle\Extract\ProductXmlExtractor;
use Strixos\IcecatConnectorBundle\Transform\ProductXmlToArrayTransformer;
use Strixos\IcecatConnectorBundle\Transform\ProductArrayToCatalogProductTransformer;

use Strixos\IcecatConnectorBundle\Extract\LanguagesExtract;
use Strixos\IcecatConnectorBundle\Extract\ProductExtract;
use Strixos\IcecatConnectorBundle\Extract\ProductsExtract;
use Strixos\IcecatConnectorBundle\Extract\SuppliersExtract;

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
    // TODO extends abstract service

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
        $extract = new SuppliersExtract();
        $extract->process();

        $loader = new BatchLoader($this->container->get('doctrine.orm.entity_manager'));
        $transform = new SuppliersTransform($loader);
        $transform->process();
    }

    public function importLanguages()
    {
        $extract = new LanguagesExtract();
        $extract->process();

        $loader = new BatchLoader($this->container->get('doctrine.orm.entity_manager'));
        $transform = new LanguagesTransform($loader);
        $transform->process();
    }

    public function importProducts()
    {
        $extract = new ProductsExtract();
        $extract->process();

        $transform = new ProductsTransform($this->container->get('doctrine.orm.entity_manager'));
        $transform->process();
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
            $baseProduct = $em->getRepository('StrixosIcecatConnectorBundle:Product')->find($productId);
            $prodId = $baseProduct->getProdId();
            $supplierName = $baseProduct->getSupplier()->getName();

            // 2. extract product xml from icecat
            $extractor = new ProductXmlExtractor();
            $extractor->process($prodId, $supplierName, $icecatLocale);
            $simpleXml = $extractor->getXmlElement();

            // 3. transform product xml to lines (associative array)
            $transformer = new ProductXmlToArrayTransformer();
            $transformer->process($simpleXml);
            $productBaseData = $transformer->getProductBaseData();
            $productFeatures = $transformer->getProductFeatures();

            // 4. transform array to pim product
            $productTypeService = $this->container->get('akeneo.catalog.model_producttype');
            $productService = $this->container->get('akeneo.catalog.model_product');
            $transformer = new ProductArrayToCatalogProductTransformer($productTypeService, $productService);
            $transformer->process($productBaseData, $productFeatures, $pimLocale);

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
        $products = $em->getRepository('StrixosIcecatConnectorBundle:Product')->findBySupplier($supplier);

        foreach ($products as $product) {
            $this->importProductFromIcecatXml($product->getId());
        }
    }
}