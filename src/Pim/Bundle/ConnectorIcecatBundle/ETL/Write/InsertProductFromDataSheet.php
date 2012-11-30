<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Write;

use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;
use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\DataSheetArrayToAttributesTransformer;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\DataSheetArrayToSetTransformer;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\DataSheetArrayToProductTransformer;

/**
 * Aims to insert product from datasheet
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InsertProductFromDataSheet
{

    /**
     * Import product from datasheet
     *
     * @param ProductManager   $productManager product manager
     * @param ProductDataSheet $datasheet      product datasheet
     * @param boolean          $flush          true to flush
     */
    public function import(ProductManager $productManager, IcecatProductDataSheet $datasheet, $flush)
    {
        // add / update attributes
        $transformer = new DataSheetArrayToAttributesTransformer($productManager, $datasheet);
        $transformer->transform();

        // add / update set
        $transformer = new DataSheetArrayToSetTransformer($productManager, $datasheet);
        $transformer->transform();

        // add / update product
        $transformer = new DataSheetArrayToProductTransformer($productManager, $datasheet);
        $transformer->transform();

        if ($flush) {
            $productManager->flush();
        }
    }

}
