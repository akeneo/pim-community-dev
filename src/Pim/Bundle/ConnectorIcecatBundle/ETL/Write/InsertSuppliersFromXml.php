<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Write;

use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;
use \XMLReader;
use Pim\Bundle\ConnectorIcecatBundle\Entity\SourceSupplier;

/**
 * Aims to insert icecat suppliers
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InsertSuppliersFromXml
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

        MemoryHelper::addValue('memory');
        $nbRow = 0;
        while ($xml->read()) {
            if ($xml->nodeType === XMLREADER::ELEMENT && $xml->name === 'SupplierMapping') {

                // get SourceSupplier from database if exists
                $supplier = $objectManager->getRepository('PimConnectorIcecatBundle:SourceSupplier')
                    ->findOneByIcecatId($xml->getAttribute('supplier_id'));
                if (!$supplier) {
                    $supplier = new SourceSupplier();
                    $supplier->setIcecatId($xml->getAttribute('supplier_id'));
                }
                $supplier->setName($xml->getAttribute('name'));
                $objectManager->persist($supplier);

                if (++$nbRow === $batchSize) {
                    $objectManager->flush();
                    $objectManager->clear();
                    $nbRow = 0;
                }

            } elseif ($xml->nodeType === XMLREADER::ELEMENT && $xml->name === 'SupplierMappings') {
                $date = $xml->getAttribute('Generated');
            }
        }
        $objectManager->flush();
    }

}
