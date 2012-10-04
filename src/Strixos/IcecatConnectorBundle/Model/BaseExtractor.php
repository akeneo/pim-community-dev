<?php
namespace Strixos\IcecatConnectorBundle\Model;

/**
 * Aims to extract base files from Open Icecat to insert them in a local
 * referential of suppliers and product ids which can be used to retrieve
 * detailled data of a product
 *
 * @author    Nicolas Dupont @ Strixos
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
use Strixos\IcecatConnectorBundle\Model\Load\ProductLoadDataInFile;

use Strixos\IcecatConnectorBundle\Model\Load\SupplierLoadDataInFile;

use Strixos\DataFlowBundle\Model\Extract\FileHttpDownload;
use Strixos\DataFlowBundle\Model\Extract\FileUnzip;
use Strixos\IcecatConnectorBundle\Model\Transform\SupplierXmlToCsv;

class BaseExtractor
{

    const URL_SUPPLIERS = 'http://data.icecat.biz/export/freeurls/supplier_mapping.xml';
    const URL_PRODUCTS = 'http://data.icecat.biz/export/freeurls/export_urls_rich.txt.gz';

    // TODO: define in configuration !!
    CONST AUTH_LOGIN    = 'NicolasDupont';
    CONST AUTH_PASSWORD = '1cec4t**)';

    protected $_entityManager;

    // TODO: add a reset to delete all files and force update!

    /**
     * Aims to inject entity manager
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->_entityManager = $em;
    }

    /**
     * Update local referencial
     */
    public function process()
    {
        // we deal with big download, ensure it will not stopped by max exec
        ini_set('max_execution_time', 0);
        // retrieve suppliers and products data
        $this->_extractAndLoadSupplierData();
        $this->_extractAndLoadBaseProductData();
    }

    /**
     * Extract supplier data from Icecat and load in local database
     */
    protected function _extractAndLoadSupplierData()
    {
        // download supplier list as xml file
        $xmlFile = '/tmp/supplier-file.xml';
        if (!file_exists($xmlFile)) {
            $downloader = new FileHttpDownload();
            $downloader->process(
                self::URL_SUPPLIERS, $xmlFile, self::AUTH_LOGIN, self::AUTH_PASSWORD
            );
            // transform xml file to csv file
            $csvFile = '/tmp/supplier-file.csv';
            $transformer = new SupplierXmlToCsv();
            $transformer->process($xmlFile, $csvFile);
            // load csv into local table
            $supplierLoader = new SupplierLoadDataInFile($this->_entityManager);
            $supplierLoader->process($csvFile);
        }
    }

    /**
    * Extract product data from Icecat and load in local database
    */
    protected function _extractAndLoadBaseProductData()
    {
        // download text product archive (only few data on products)
        $pathProductArchive = '/tmp/export_urls_rich.txt.gz';
        if (!file_exists($pathProductArchive)) {
            $downloader = new FileHttpDownload();
            $downloader->process(
                self::URL_PRODUCTS, $pathProductArchive,
                self::AUTH_LOGIN, self::AUTH_PASSWORD
            );
            // extract product archive to get text file
            $pathProductFile = '/tmp/export_urls_rich.txt';
            if (!file_exists($pathProductFile)) {
                $unzipper = new FileUnzip();
                $unzipper->process($pathProductArchive, $pathProductFile);
            }
            // load csv into local table
            $supplierLoader = new ProductLoadDataInFile($this->_entityManager);
            $supplierLoader->process($pathProductFile);
        }
    }

}
