<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Extract;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpReader;

use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;
use Pim\Bundle\ConnectorIcecatBundle\Entity\ConfigManager;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Read;

/**
 * Get product xml details from icecat
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductXmlExtractor implements ExtractInterface
{
    /**
     * Get xml product content
     * @var SimpleXMLElement
     */
    protected $xmlElement;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $url;

    /**
     * Constructor
     *
     * @param string        $productId
     * @param string        $supplierName
     * @param string        $locale
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
     * @param  string $baseUrl
     * @param  string $productId
     * @param  string $supplierName
     * @param  string $locale
     * @return string
     */
    protected static function prepareUrl($baseUrl, $productId, $supplierName, $locale)
    {
        $supplierName = rawurlencode($supplierName);
        $productId    = rawurlencode($productId);

        return $baseUrl .'?prod_id='.$productId.';vendor='.$supplierName.';lang='.$locale.';output=productxml';
    }

    /**
     * (non-PHPdoc)
     * @see Pim\Bundle\ConnectorIcecatBundle\Extract.ExtractInterface::extract()
     */
    public function extract()
    {
        $stringXml = $this->read($this->url);
        $this->parseXml($stringXml);
        $this->checkResponse();
    }

    /**
     * (non-PHPdoc)
     * @see Pim\Bundle\ConnectorIcecatBundle\Extract.ReadInterface::read()
     */
    public function read($url)
    {
        $fileReader = new FileHttpReader();

        return $fileReader->process($url, $this->login, $this->password);
    }

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
     * @see Pim\Bundle\ConnectorIcecatBundle\Extract.ReadInterface::getReadContent()
     */
    public function getReadContent()
    {
        return $this->xmlElement;
    }
}
