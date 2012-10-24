<?php
namespace Strixos\IcecatConnectorBundle\Extract;

use Strixos\IcecatConnectorBundle\Entity\Config;

use Strixos\DataFlowBundle\Model\Extract\FileUnzip;
use Strixos\DataFlowBundle\Model\Extract\FileHttpDownload;

/**
 * Get product xml details from icecat
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductXmlExtractor implements ExtractInterface, DownloadInterface, UnpackInterface
{

    /*const AUTH_LOGIN    = 'NicolasDupont';
    const AUTH_PASSWORD = '1cec4t**)';

    const BASE_URL         = 'http://data.Icecat.biz/xml_s3/xml_server3.cgi';
    const XML_FILE_ARCHIVE = '/tmp/suppliers-list.xml.gz';
    const XML_FILE         = '/tmp/suppliers-list.xml';*/

    /**
     * Get xml product content
     * @var SimpleXMLElement
     */
    protected $xmlElement;

    /**
     * Constructor
     *
     * @param string $productId
     * @param string $supplierName
     * @param string $locale
     * @param ConfigManager $configManager
     */
    public function __construct($productId, $supplierName, $locale, $configManager, $forceDownloadFile = false)
    {
    	// TODO : this 3 parameters must be deleted (not instance variable just local to define download paths and url)
        $this->productId = $productId;
        $this->supplierName = $supplierName;
        $this->locale = $locale;
        
        $this->forceDownload = $forceDownloadFile;
        
        // get configuration from config manager
        $this->login = $configManager->getValue(Config::LOGIN);
        $this->password = $configManager->getValue(Config::PASSWORD);
        $baseDir = $configManager->getValue(Config::BASE_DIR);
        
        $baseFilePath = $baseDir . $configManager->getValue(Config::PRODUCT_FILE);
        $baseArchiveFilePath = $baseDir . $configManager->getValue(Config::PRODUCT_ARCHIVED_FILE);
        $baseUrl = $configManager->getValue(Config::PRODUCT_URL);
        
        // prepare url and paths
        $this->url = self::prepareUrl($baseUrl, $productId, $supplierName, $locale);
        $this->archiveFilePath = self::preparePath($baseFilePath, $productId, $locale);
        $this->filePath = self::preparePath($baseArchiveFilePath, $productId, $locale);
    }
    
    /**
     * Prepare path for downloading and extracting data
     * 
     * @static
     * @param string $basePath
     * @param string $productId
     * @param string $locale
     * @return string
     */
    protected static function preparePath($basePath, $productId, $locale)
    {
    	$path = str_replace('%%product_id%%', self::slugify($productId), $basePath);
    	return str_replace('%%locale%%', $locale, $path);
    }
    
    /**
     * Slugify a string to be used in filepath or url
     * 
     * @static
     * @param string $name
     * @return string
     */
    protected static function slugify($name)
    {
        $search = array('/', ' ');
        $replace = array('', '');
        return str_replace($search, $replace, $name);
    }

    /**
     * Prepare url to get xml content
     *
     * @static
     * @param string $baseUrl
     * @param string $productId
     * @param string $supplierName
     * @param string $locale
     * @return string
     */
    protected static function prepareUrl($baseUrl, $productId, $supplierName, $locale)
    {
        $supplierName = rawurlencode($supplierName);
        $productId    = rawurlencode($productId);
        return $baseUrl .'?prod_id='.$productId.';vendor='.$supplierName.';lang='.$locale.';output=productxml';
    }

    /**
     * Get xml content
     *
     * @param string $productId
     * @param string $supplierName
     * @param string $locale
     */
    public function extract()
    {
    	$this->download($this->url, $this->archiveFilePath);
    	$this->unpack($this->archiveFilePath, $this->filePath);
//         $url = $this->prepareUrl($this->productId, $this->supplierName, $this->locale);
        $stringXml = $this->getXmlString($this->url);
        $this->parseXml($stringXml);
        $this->checkResponse();
    }
    
    /**
     * (non-PHPdoc)
     * @see \Strixos\IcecatConnectorBundle\Extract\DownloadInterface::download()
     */
    public function download($url, $file)
    {
    	$downloader = new FileHttpDownload();
    	$downloader->process($url, $file, $this->login, $this->password, $this->forceDownload);
    }
    
    /**
     * (non-PHPdoc)
     * @see \Strixos\IcecatConnectorBundle\Extract\UnpackInterface::unpack()
     */
    public function unpack($archivedFile, $file)
    {
    	$unpacker = new FileUnzip();
    	$unpacker->process($archivedFile, $file, $this->forceDownload);
    }

    /**
     * Get xml product content
     *
     * @param string $url
     * @throws Exception
     * @return string
     */
    protected function getXmlString($url)
    {
        // use curl to get xml product content with basic authentication
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HEADER, false);
        curl_setopt($c, CURLOPT_USERPWD, $this->login.':'.$this->password);
        $output = curl_exec($c);
        // deal with curl exception
        if ($output === false) {
            throw new Exception('Curl Error : '.curl_error($c));
        }
        curl_close($c);
        return $output;
    }

    /**
     * Parse xml response
     * @param SimpleXMLElement $stringXml
     * @return boolean
     */
    protected function parseXml($stringXml)
    {
        libxml_use_internal_errors(true);
        $this->xmlElement = simplexml_load_string($stringXml);
        if ($this->xmlElement) {
            return true;
        }
        $this->xmlElement = simplexml_load_string(utf8_encode($stringXml));
        if ($this->xmlElement) {
            return true;
        }
        return false;
    }

    /**
     * Check Icecat response content
     * @return boolean
     */
    protected function checkResponse()
    {
        // TODO to raise authentication error or product with no detailled data
        return true;
    }

    /**
     * Get xml
     * @return SimpleXMLElement
     */
    public function getXmlElement()
    {
        return $this->xmlElement;
    }

}