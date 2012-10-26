<?php
namespace Strixos\IcecatConnectorBundle\Transform;

use Strixos\IcecatConnectorBundle\Entity\SourceSupplier;

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
     * @var LoadInterface
     */
    protected $loader;
    
    /**
     * @var string
     */
    protected $xmlContent;

    /**
     * Constructor
     * @param LoadInterface $loader
     * @param string $xmlContent
     */
    public function __construct($loader, $xmlContent)
    {
        $this->loader = $loader;
        $this->xmlContent = $xmlContent;
    }

    /**
     * (non-PHPdoc)
     * @see Strixos\IcecatConnectorBundle\Transform.LanguagesTransform::transform()
     */
    public function transform()
    {
        // read xml document and parse to suppliers entities
        $xml = new XMLReader();
        $xml->XML($this->xmlContent);
//         $xml->open($this->filePath);

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
