<?php
namespace Strixos\IcecatConnectorBundle\Transform;

use Strixos\IcecatConnectorBundle\Entity\SourceSupplier;

use Strixos\IcecatConnectorBundle\Load\SupplierLoad;
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
class SuppliersTransform extends IcecatTransform
{

    const URL              = 'http://data.icecat.biz/export/freeurls/supplier_mapping.xml';
    const XML_FILE_ARCHIVE = '/tmp/suppliers-list.xml.gz';
    const XML_FILE         = '/tmp/suppliers-list.xml';

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
    public function process()
    {
        // read xml document and parse to suppliers entities
        $xml = new XMLReader();
        $xml->open(self::XML_FILE);

        while ($xml->read()) {
            if ($xml->nodeType === XMLREADER::ELEMENT && $xml->name === 'SupplierMapping') {
                $supplier = new SourceSupplier();
                $supplier->setIcecatId($xml->getAttribute('supplier_id'));
                $supplier->setName($xml->getAttribute('name'));
                $this->loader->add($supplier);
            } else if ($xml->nodeType === XMLREADER::ELEMENT && $xml->name === 'SupplierMappings') {
                $date = $xml->getAttribute('Generated');
            }
        }

        $this->loader->load();
    }
}
