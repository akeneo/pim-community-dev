<?php
namespace Strixos\IcecatConnectorBundle\Model;

use Strixos\IcecatConnectorBundle\Model\Service\LanguagesService;

use Strixos\IcecatConnectorBundle\Model\Service\SuppliersService;

use Strixos\IcecatConnectorBundle\Model\Import\ProductImportDataFromXml;
use Strixos\IcecatConnectorBundle\Model\Import\SupplierImportDataFromXml;
use Strixos\IcecatConnectorBundle\Model\Import\ProductImportDataFromCsv;
use Strixos\IcecatConnectorBundle\Model\Import\LanguageImportDataFromXml;

use Strixos\DataFlowBundle\Model\Extract\FileHttpDownload;
use Strixos\DataFlowBundle\Model\Extract\FileUnzip;

/**
 * Aims to load data from icecat to local database
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class BaseExtractor
{
    // TODO: define in configuration !!
    const URL_SUPPLIERS = 'http://data.icecat.biz/export/freeurls/supplier_mapping.xml';
    const URL_PRODUCTS = 'http://data.icecat.biz/export/freeurls/export_urls_rich.txt.gz';
    const URL_LANGUAGES = 'https://data.icecat.biz/export/freexml/refs/LanguageList.xml.gz';
    const URL_PRODUCT = 'http://data.Icecat.biz/xml_s3/xml_server3.cgi';
    const AUTH_LOGIN    = 'NicolasDupont';
    const AUTH_PASSWORD = '1cec4t**)';

    /**
     * Entity Manager
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    // TODO: add a reset to delete all files and force update!

    /**
     * Aims to inject entity manager
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->entityManager = $em;
        $this->initialize();
    }

    /**
     * Initialize method used here to change php environment
     */
    public function initialize()
    {
        // we deal with big download, ensure it will not stopped by max exec
        ini_set('max_execution_time', 0);
    }

    /**
     * Update local referencial
     */
    public function process()
    {
        // retrieve suppliers and products data
        $this->extractAndImportSupplierData();
        $this->extractAndImportProductData();
    }

    /**
     * Extract supplier data from Icecat and load in local database
     */
    public function extractAndImportSupplierData()
    {
        $xmlFileArchive = '/tmp/suppliers-list.xml.gz';
        $xmlFile = '/tmp/suppliers-list.xml';

        // -1- Download suppliers list in /tmp/...
        /*$downloader = new FileHttpDownload();
        $downloader->process(self::URL_SUPPLIERS, $xmlFileArchive, self::AUTH_LOGIN, self::AUTH_PASSWORD, false);

        // -2- Unzip file
        $unzipper = new FileUnzip();
        $unzipper->process($xmlFileArchive, $xmlFile);*/
        $srv = new SuppliersService($this->entityManager);
        $srv->process();

        // -3- Call XML Loader to save in database
        $loader = new SupplierImportDataFromXml($this->entityManager);
        $loader->process($xmlFile);
    }

    /**
     * Extract product data from Icecat and load in local database
     */
    public function extractAndImportProductData()
    {
        $txtFileArchive = '/tmp/export_urls_rich.txt.gz';
        $txtFile = '/tmp/export_urls_rich.txt';

        // download text product archive (only few data on products)
        $downloader = new FileHttpDownload();
        $downloader->process(
            self::URL_PRODUCTS, $txtFileArchive,
            self::AUTH_LOGIN, self::AUTH_PASSWORD, false
        );

        // extract product archive to get text file
         $unzipper = new FileUnzip();
         $unzipper->process($txtFileArchive, $txtFile, false);

        // load csv into local table
        $loader = new ProductImportDataFromCsv($this->entityManager);
        $loader->process($txtFile);
    }
    
    public function extractAndImportProduct($prodId, $supplierName, $locale)
    {
        // TODO : see URL encode if necessary
        $urlProduct = self::URL_PRODUCT .
                '?prod_id='.$prodId.';vendor='.$supplierName.';lang='.$locale.';output=productxml';
        $xmlFile = '/tmp/product-'. $prodId .'-'. $locale .'.xml';
        
        // Download product xml file
        $downloader = new FileHttpDownload();
        $downloader->process($urlProduct, $xmlFile, self::AUTH_LOGIN, self::AUTH_PASSWORD);
        
        // Import data
        $loader = new ProductImportDataFromXml($this->entityManager);
        $loader->process($xmlFile);
    }

    /**
     * Extract languages from Icecat and load in local database
     */
    public function extractAndImportLanguages()
    {
        $xmlFileArchive = '/tmp/languages-list.xml.gz';
        $xmlFile = '/tmp/languages-list.xml';

        // -1- Download suppliers list in /tmp/...
        /*$downloader = new FileHttpDownload();
        $downloader->process(self::URL_LANGUAGES, $xmlFileArchive, self::AUTH_LOGIN, self::AUTH_PASSWORD, false);

        // -2- Unzip file
        $unzipper = new FileUnzip();
        $unzipper->process($xmlFileArchive, $xmlFile);*/
        $srv = new LanguagesService($this->entityManager);
        $srv->process();

        // -3- Call XML Loader to save in database
        $loader = new LanguageImportDataFromXml($this->entityManager);
        $loader->process($xmlFile);
    }
}
