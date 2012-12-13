<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Write;

use Doctrine\Common\Persistence\ObjectManager;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Interfaces\WriteInterface;

use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Entity\SourceLanguage;
use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;

/**
 * Aims to insert icecat languages
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LanguagesFromXmlWriter implements WriteInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Constructor
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Import data from file to local database
     *
     * @param string  $xmlContent xml content
     * @param integer $batchSize  batch size
     *
     * TODO : Make refactoring -> must call transformer
     */
    public function write($xmlContent, $batchSize = 200)
    {
        $xml = new \XMLReader();
        $xml->XML($xmlContent);

        $nbRow = 0;
        while ($xml->read()) {
            if ($xml->nodeType === \XMLREADER::ELEMENT && $xml->name === 'Language') {
                $shortCode = $this->formatShortCode($xml->getAttribute('ShortCode'));

                $lang = new SourceLanguage();
                $lang->setCode($xml->getAttribute('Code'));
                $lang->setShortCode($shortCode);
                $lang->setIcecatShortCode($xml->getAttribute('ShortCode'));
                $lang->setIcecatId($xml->getAttribute('ID'));

                $this->objectManager->persist($lang);

                if (++$nbRow === $batchSize) {
                    $this->objectManager->flush();
                    $this->objectManager->clear();
                    $nbRow = 0;
                }

            } elseif ($xml->nodeType === \XMLREADER::ELEMENT && $xml->name === 'Response') {
                $date = $xml->getAttribute('Date');
            }
        }
        $this->objectManager->flush();
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
