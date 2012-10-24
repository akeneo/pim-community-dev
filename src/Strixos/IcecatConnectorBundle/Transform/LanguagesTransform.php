<?php
namespace Strixos\IcecatConnectorBundle\Transform;

use Strixos\IcecatConnectorBundle\Entity\SourceLanguage;

use \XMLReader;

/**
 * Aims to transform suppliers xml file to csv file
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : MAKE interfaces to implements xml to csv, xml to php, csv to php, etc.
 */
class LanguagesTransform implements TransformInterface
{
    protected $loader;

    /**
     * Constructor
     * @param SupplierLoader $loader
     */
    public function __construct($loader)
    {
        //$this->container = $container;
        $this->loader = $loader;
    }

    /**
     * Transform xml file to csv
     *
     * @param string $xmlFile
     * @param string $csvFile
     */
    public function transform()
    {
        // read xml document and parse to suppliers entities
        $xml = new XMLReader();
        $xml->open(self::XML_FILE);

        while ($xml->read()) {
            if ($xml->nodeType === XMLREADER::ELEMENT && $xml->name === 'Language') {
                $shortCode = $this->formatShortCode($xml->getAttribute('ShortCode'));

                $lang = new SourceLanguage();
                $lang->setCode($xml->getAttribute('Code'));
                $lang->setShortCode($shortCode);
                $lang->setIcecatShortCode($xml->getAttribute('ShortCode'));
                $lang->setIcecatId($xml->getAttribute('ID'));

                $this->loader->add($lang);
            } else if ($xml->nodeType === XMLREADER::ELEMENT && $xml->name === 'Response') {
                $date = $xml->getAttribute('Date');
            }
        }

        $this->loader->load();
    }

    /**
     * Formatter for short code language
     *
     * @param string $shortCode
     * @return string
     */
    private function formatShortCode($shortCode)
    {
        $length = strlen($shortCode);
        if ($length === 2) {
            $shortCode = strtolower($shortCode).'_'.strtoupper($shortCode);
        } elseif ($length === 5) {
            $tmpCode = explode('_', $shortCode);
            $shortCode = strtolower($tmpCode[0]) .'_'. strtoupper($tmpCode[1]);
        } else {
            throw new \Exception('Incorrect short code format');
        }
        return $shortCode;
    }
}
