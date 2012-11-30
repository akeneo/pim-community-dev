<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Transform;

use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;
use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;

/**
 * Aims to transform product data sheet data to attribute instances
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataSheetArrayToAttributesTransformer implements TransformInterface
{
    /**
     * @staticvar string
     */
    const PREFIX = 'icecat';

    /**
     * Get product manager service
     * @var Service
     */
    protected $productManager;

    /**
     * Product data sheet to transform
     * @var ProductDataSheet
     */
    protected $datasheet;

    /**
     * Constructor
     *
     * @param ProductManager   $productManager product manager
     * @param ProductDataSheet $datasheet      product datasheet
     */
    public function __construct(\Pim\Bundle\CatalogBundle\Doctrine\ProductManager $productManager, IcecatProductDataSheet $datasheet)
    {
        $this->productManager = $productManager;
        $this->datasheet = $datasheet;
    }

    /**
     * {@ineritedDoc}
     *
     * @return
     */
    public function transform()
    {



    }
}
