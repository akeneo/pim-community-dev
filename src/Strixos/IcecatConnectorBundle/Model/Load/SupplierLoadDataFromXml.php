<?php
namespace Strixos\IcecatConnectorBundle\Model\Load;

use \XMLReader as XMLReader;

use Strixos\IcecatConnectorBundle\Entity\Supplier;

/**
 * Load supplier data from icecat xml files
 * 
 * @author    Romain Monceau @ Akeneo
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SupplierLoadDataFromXml extends DataLoader
{
    /**
     * Read Xml file, create entities and save them
     * @param string $xmlFile
     */
    public function process($xmlFile)
    {
        // define batch size and initialize variables
        $batchSize = 2500;
        $i=0;
    
        // -3- Read xml document and parse to entities (suppliers)
        $xml = new XMLReader();
        $xml->open($xmlFile);
    
        while ($xml->read())
        {
            if ($xml->name === 'Supplier') {
                $supplier = new Supplier();
                $supplier->setIcecatId($xml->getAttribute('ID'));
                $supplier->setName($xml->getAttribute('Name'));
                $this->_entityManager->persist($supplier);
    
                // Insert by groups
                if (++$i % $batchSize === 0) {
                    $this->_entityManager->flush();
                    $this->_entityManager->clear();
                }
    
    
            } else if ($xml->name === 'Response') {
                $date = $xml->getAttribute('Date');
            }
        }
    
        $this->_entityManager->flush();
    }
}