<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Write;

use Doctrine\Common\Persistence\ObjectManager;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Interfaces\WriteInterface;

use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Entity\SourceSupplier;
use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;

/**
 * Aims to insert icecat suppliers
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SuppliersFromXmlWriter implements WriteInterface
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

        MemoryHelper::addValue('memory');
        $nbRow = 0;
        while ($xml->read()) {
            if ($xml->nodeType === \XMLREADER::ELEMENT && $xml->name === 'SupplierMapping') {

                // get SourceSupplier from database if exists
                $supplier = $this->objectManager->getRepository('PimConnectorIcecatBundle:SourceSupplier')
                    ->findOneByIcecatId($xml->getAttribute('supplier_id'));
                if (!$supplier) {
                    $supplier = new SourceSupplier();
                    $supplier->setIcecatId($xml->getAttribute('supplier_id'));
                }
                $supplier->setName($xml->getAttribute('name'));
                $this->objectManager->persist($supplier);

                if (++$nbRow === $batchSize) {
                    $this->objectManager->flush();
                    $this->objectManager->clear();
                    $nbRow = 0;
                }

            } elseif ($xml->nodeType === \XMLREADER::ELEMENT && $xml->name === 'SupplierMappings') {
                $date = $xml->getAttribute('Generated');
            }
        }
        $this->objectManager->flush();
    }

}
