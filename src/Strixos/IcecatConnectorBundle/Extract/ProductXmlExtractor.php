<?php
namespace Strixos\IcecatConnectorBundle\Extract;

use Strixos\DataFlowBundle\Model\Extract\FileHttpReader;

use Strixos\IcecatConnectorBundle\Entity\Config;
use Strixos\IcecatConnectorBundle\Entity\ConfigManager;

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
    public function __construct($productId, $supplierName, $locale, ConfigManager $configManager)
    {
        // get configuration from config manager
        $this->login = $configManager->getValue(Config::LOGIN);
        $this->password = $configManager->getValue(Config::PASSWORD);
        $baseUrl = $configManager->getValue(Config::PRODUCT_URL);
        
        // prepare url and paths
        $this->url = self::prepareUrl($baseUrl, $productId, $supplierName, $locale);
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
        $stringXml = $this->read($this->url);
        $this->parseXml($stringXml);
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
        return $fileReader->process($url, $this->login, $this->password);
    }

    /**
     * Parse xml response
     * 
     * @param string $stringXml
     * @return boolean
     * 
     * TODO : Must be in Transform ?
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
     * (non-PHPdoc)
     * @see Strixos\IcecatConnectorBundle\Extract.ReadInterface::getReadContent()
     */
    public function getReadContent()
    {
        return $this->xmlElement;
    }
}