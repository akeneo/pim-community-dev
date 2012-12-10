<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Write;

use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;
use \XMLReader;
use Pim\Bundle\ConnectorIcecatBundle\Entity\SourceLanguage;

/**
 * Aims to insert icecat languages
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LanguagesFromXmlWriter
{

    /**
     * Import data from file to local database
     *
     * @param string        $xmlContent    xml content
     * @param ObjectManager $objectManager manager
     * @param integer       $batchSize     batch size
     */
    public function import($xmlContent, $objectManager, $batchSize = 200)
    {
        $xml = new XMLReader();
        $xml->XML($xmlContent);

        $nbRow = 0;
        while ($xml->read()) {
            if ($xml->nodeType === XMLREADER::ELEMENT && $xml->name === 'Language') {
                $shortCode = $this->formatShortCode($xml->getAttribute('ShortCode'));

                $lang = new SourceLanguage();
                $lang->setCode($xml->getAttribute('Code'));
                $lang->setShortCode($shortCode);
                $lang->setIcecatShortCode($xml->getAttribute('ShortCode'));
                $lang->setIcecatId($xml->getAttribute('ID'));

                $objectManager->persist($lang);

                if (++$nbRow === $batchSize) {
                    $objectManager->flush();
                    $objectManager->clear();
                    $nbRow = 0;
                }

            } elseif ($xml->nodeType === XMLREADER::ELEMENT && $xml->name === 'Response') {
                $date = $xml->getAttribute('Date');
            }
        }
        $objectManager->flush();
    }

    /**
     * Formatter for short code language
     *
     * TODO :refactor !
     *
     * @param string $shortCode
     *
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
            throw new Exception('Incorrect short code format');
        }

        return $shortCode;
    }

}
