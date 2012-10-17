<?php
namespace Strixos\IcecatConnectorBundle\Extract;

use Strixos\IcecatConnectorBundle\Model\Service\ProductService;

/**
 * Get product xml details from icecat
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductXmlExtractor
{

    /**
     * Get xml product content
     * @var SimpleXMLElement
     */
    protected $xmlElement;

    /**
     * Get xml content
     *
     * @param string $productId
     * @param string $supplierName
     * @param string $locale
     */
    public function process($productId, $supplierName, $locale)
    {
        $url = $this->prepareUrl($productId, $supplierName, $locale);
        $stringXml = $this->getXmlString($url);
        $this->parseXml($stringXml);
        $this->checkResponse();
    }

    /**
     * Prepare url to get xml content
     *
     * @param string $productId
     * @param string $supplierName
     * @param string $locale
     */
    protected function prepareUrl($productId, $supplierName, $locale)
    {
        $search = array('/', ' ');
        $replace = array('', '');
        $this->filePath = '/tmp/product-'. str_replace($search, $replace, $productId) .'-'. $locale .'.xml';
        $this->fileArchivePath = $this->filePath .'.gz';
        $supplierName = rawurlencode($supplierName);
        $productId    = rawurlencode($productId);
        return ProductService::BASE_URL .'?prod_id='.$productId.';vendor='.$supplierName.';lang='.$locale.';output=productxml';
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
        curl_setopt($c, CURLOPT_USERPWD, IcecatExtract::AUTH_LOGIN.':'.IcecatExtract::AUTH_PASSWORD);
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