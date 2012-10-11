<?php
namespace Strixos\IcecatConnectorBundle\Model\Import;

use \XMLReader as XMLReader;

use Strixos\IcecatConnectorBundle\Entity\Language;

/**
 * Import language data from an icecat XML file
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LanguageImportDataFromXml extends DataImport
{
    /**
     * (non-PHPdoc)
     * @see \Strixos\IcecatConnectorBundle\Model\Import\DataImport::process()
     */
    public function process($xmlFile)
    {
        // read xml document and parse to suppliers entities
        $xml = new XMLReader();
        $xml->open($xmlFile);
        
        while ($xml->read()) {
            if ($xml->nodeType === XMLREADER::ELEMENT && $xml->name === 'Language') {
            	$shortCode = $this->formatShortCode($xml->getAttribute('ShortCode'));
            	
                $lang = new Language();
                $lang->setCode($xml->getAttribute('Code'));
                $lang->setShortCode($shortCode);
                $lang->setIcecatShortCode($xml->getAttribute('ShortCode'));
                $lang->setIcecatId($xml->getAttribute('ID'));
                $this->entityManager->persist($lang);
            } else if ($xml->nodeType === XMLREADER::ELEMENT && $xml->name === 'Response') {
                $date = $xml->getAttribute('Date');
            }
        }
        
        $this->entityManager->flush();
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