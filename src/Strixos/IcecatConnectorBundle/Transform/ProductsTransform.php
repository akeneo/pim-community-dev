<?php
namespace Strixos\IcecatConnectorBundle\Transform;

use Strixos\IcecatConnectorBundle\Entity\SourceProduct;

use Strixos\IcecatConnectorBundle\Load\BatchLoader;

/**
 * Aims to transform suppliers xml file to csv file
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : MAKE interfaces to implements xml to csv, xml to php, csv to php, etc.
 */
class ProductsTransform extends IcecatTransform
{

    const URL          = 'http://data.icecat.biz/export/freeurls/export_urls_rich.txt.gz';
    const FILE_ARCHIVE = '/tmp/export_urls_rich.txt.gz';
    const FILE         = '/tmp/export_urls_rich.txt';

    protected $loader;

    /**
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Constructor
     * @param EntityManager $loader
     */
    public function __construct($em)
    {
        $this->entityManager = $em;
        $this->loader = new BatchLoader($this->entityManager);
    }

    /**
     * Transform xml file to csv
     *
     * @param string $xmlFile
     * @param string $csvFile
     */
    public function process()
    {
        // get associative array with suppliers icecat ids and suppliers
        $suppliers = $this->entityManager->getRepository('StrixosIcecatConnectorBundle:SourceSupplier')->findAll();
        $this->_icecatIdToSupplier = array();
        foreach ($suppliers as $supplier) {
            $this->_icecatIdToSupplier[$supplier->getIcecatId()] = $supplier;
        }

        // import products
        if (($handle = fopen(self::FILE, 'r')) !== false) {
            $length = 1000;
            $delimiter = "\t";
            $indRow = 0;
            $batchSize = 5000;
            while (($data = fgetcsv($handle, $length, $delimiter)) !== false) {
                // not parse header
                if ($indRow++ == 0) {
                    continue;
                }
                // inject as product
                $product = new SourceProduct();
                $product->setProductId($data[0]);
                // TODO: get real supplier id problem with mapping
                $product->setSupplier($this->_icecatIdToSupplier[$data[4]]);
                $product->setProdId($data[1]);
                $product->setMProdId($data[10]);
                $this->loader->add($product);

                if ($indRow % $batchSize === 0) {
                    $this->loader->load();
                    return; // TODO : must be deleted !! Actually create a bug with clean method
                }
            }
            $this->loader->load();
        }
    }
}
