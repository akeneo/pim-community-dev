<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Transform;

use Pim\Bundle\ConnectorIcecatBundle\Load\BatchLoader;

use Doctrine\ORM\EntityManager;

use Pim\Bundle\ConnectorIcecatBundle\Entity\SourceSupplier;

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
class SuppliersTransform implements TransformInterface
{
    /**
     * @var BatchLoader
     */
    protected $loader;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $xmlContent;

    /**
     * Constructor
     * @param EntityManager $em
     * @param string        $xmlContent
     */
    public function __construct(EntityManager $em, $xmlContent)
    {
        $this->entityManager = $em;
        $this->loader = new BatchLoader($this->entityManager);
        $this->xmlContent = $xmlContent;
    }

    /**
     * (non-PHPdoc)
     * @see Pim\Bundle\ConnectorIcecatBundle\Transform.LanguagesTransform::transform()
     */
    public function transform()
    {
        // read xml document and parse to suppliers entities
        $xml = new XMLReader();
        $xml->XML($this->xmlContent);
//         $xml->open($this->filePath);

        while ($xml->read()) {
            if ($xml->nodeType === XMLREADER::ELEMENT && $xml->name === 'SupplierMapping') {

                // get SourceSupplier from database if exists
                $supplier = $this->entityManager->getRepository('PimConnectorIcecatBundle:SourceSupplier')
                        ->findOneByIcecatId($xml->getAttribute('supplier_id'));
                if (!$supplier) {
                    $supplier = new SourceSupplier();
                    $supplier->setIcecatId($xml->getAttribute('supplier_id'));
                }
                $supplier->setName($xml->getAttribute('name'));
                $this->loader->add($supplier);
            } elseif ($xml->nodeType === XMLREADER::ELEMENT && $xml->name === 'SupplierMappings') {
                $date = $xml->getAttribute('Generated');
            }
        }

        $this->loader->load();
    }
}
