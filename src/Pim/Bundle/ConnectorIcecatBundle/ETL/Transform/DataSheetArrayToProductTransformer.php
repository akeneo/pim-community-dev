<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Transform;

use Pim\Bundle\CatalogBundle\Form\DataTransformer\ProductToArrayTransformer;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Interfaces\TransformInterface;
use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;
use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;

/**
 * Aims to transform product data sheet data to catalog product instance
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataSheetArrayToProductTransformer implements TransformInterface
{
    /**
     * @staticvar string
     */
    const PREFIX = 'icecat';

    /**
     * Get product manager service
     * @var \Pim\Bundle\CatalogBundle\Doctrine\ProductManager
     */
    protected $productManager;

    /**
     * Product data sheet to transform
     * @var IcecatProductDataSheet
     */
    protected $datasheet;

    /**
     * Constructor
     *
     * @param ProductManager         $productManager product manager
     * @param IcecatProductDataSheet $datasheet      product datasheet
     */
    public function __construct(\Pim\Bundle\CatalogBundle\Doctrine\ProductManager $productManager, IcecatProductDataSheet $datasheet)
    {
        $this->productManager = $productManager;
        $this->datasheet = $datasheet;
    }

    /**
     * {@inheritdoc}
     */
    public function transform()
    {
        $localeIcecat = 1; // en_us

        // get datas
        $allData = json_decode($this->datasheet->getData(), true);
        $prodData = $allData['basedata'];
        $prodFeatureData = $allData['productfeatures'];

        // prepare product data
        $productData = array(
            'id'     => null,
            'sku'    => $prodData['id'],
            'values' => array()
        );

        // add attributes values to product
        foreach ($prodFeatureData as $icecatAttId => $feature) {
            $attCode = self::PREFIX .'-'. $icecatAttId;
            $productData['values'][$attCode] = $feature['Value'][$localeIcecat];
        }

        // initialize data transformer and transform data to product entity
        $dataTransformer = new ProductToArrayTransformer($this->productManager);
        $product = $dataTransformer->reverseTransform($productData);

        // persist product
        $this->productManager->getPersistenceManager()->persist($product);
    }
}
