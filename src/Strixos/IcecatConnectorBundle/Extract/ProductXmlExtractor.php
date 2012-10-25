<?php
namespace Strixos\IcecatConnectorBundle\Extract;

use Strixos\DataFlowBundle\Model\Extract\FileHttpReader;

use Strixos\IcecatConnectorBundle\Entity\Config;

/**
 * Get product xml details from icecat
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductXmlExtractor implements ExtractInterface, ReadInterface
{
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
    public function __construct($productId, $supplierName, $locale, $configManager)
    {
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
        $this->read($this->url);
        $this->parseXml($this->xmlContent);
        $this->checkResponse();
    }

    /**
     * Get xml product content
     *
     * @param string $url
     * @throws Exception
     * @return string
     */
    public function read($url)
    {
    	$fileReader = new FileHttpReader();
        $this->xmlContent = $fileReader->process($url, $this->login, $this->password);
    }

    /**
     * Parse xml response
     * @param SimpleXMLElement $stringXml
     * @return boolean
     */
    protected function parseXml($stringXml)
    {
        libxml_use_internal_errors(true);
        $this->xmlContent = simplexml_load_string($stringXml);
        if ($this->xmlContent) {
            return true;
        }
        $this->xmlContent = simplexml_load_string(utf8_encode($stringXml));
        if ($this->xmlContent) {
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
     * (non-PHPdoc)
     * @see Strixos\IcecatConnectorBundle\Extract.ReadInterface::getReadContent()
     */
    public function getReadContent()
    {
    	return $this->xmlContent;
    }
}