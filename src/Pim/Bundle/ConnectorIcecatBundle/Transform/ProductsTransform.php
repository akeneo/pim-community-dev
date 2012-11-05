<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Transform;

use Doctrine\ORM\EntityManager;

use Pim\Bundle\ConnectorIcecatBundle\Entity\SourceProduct;

use Pim\Bundle\ConnectorIcecatBundle\Load\BatchLoader;

use \Exception;

/**
 * Aims to transform suppliers xml file to csv file
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : MAKE interfaces to implements xml to csv, xml to php, csv to php, etc.
 */
class ProductsTransform implements TransformInterface
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
    protected $filePath;

    /**
     * Constructor
     * @param EntityManager $em
     * @param string $filePath
     */
    public function __construct(EntityManager $em, $filePath)
    {
        $this->entityManager = $em;
        $this->loader = new BatchLoader($this->entityManager);
        $this->filePath = $filePath;
    }

    /**
     * (non-PHPdoc)
     * @see Pim\Bundle\ConnectorIcecatBundle\Transform.LanguagesTransform::transform()
     */
    public function transform()
    {
        // get associative array with suppliers icecat ids and suppliers
        $suppliers = $this->entityManager->getRepository('PimConnectorIcecatBundle:SourceSupplier')->findAll();
        $this->_icecatIdToSupplier = array();
        foreach ($suppliers as $supplier) {
            $this->_icecatIdToSupplier[$supplier->getIcecatId()] = $supplier;
        }
        
        // throw exception if no suppliers already imported
        if (!$suppliers) {
            throw new Exception('Suppliers must be imported before products. Please try to import suppliers.');
        }

        // import products
        if (($handle = fopen($this->filePath, 'r')) !== false) {
            $length = 1000;
            $delimiter = "\t";
            $indRow = 0;
            $batchSize = 5000;
            while (($data = fgetcsv($handle, $length, $delimiter)) !== false) {
                // not parse header
                if ($indRow++ == 0) {
                    continue;
                }
                
                // Get product if already exists
                $product = $this->entityManager->getRepository('PimConnectorIcecatBundle:SourceProduct')
                        ->findOneByProductId($data[0]);
                if (!$product) {
                    $product = new SourceProduct();
                    $product->setProductId($data[0]);
                }
                
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
