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
 * Aims to insert a collection of sets from a collection of IcecatProductDataSheet
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SetsFromDataSheetsWriter
{
    /**
     * Import a collection of sets from datasheets
     *
     * @param ProductManager $productManager product manager
     * @param array          $dataSheets     array of product datasheet
     * @param boolean        $flush          true to flush
     */
    public function import(ProductManager $productManager, $dataSheets, $flush)
    {
        $setsCode = array();

        // Call transformer for each datasheet
        foreach ($dataSheets as $dataSheet) {
            // verify if set code already in datasheet
            $allData = json_decode($dataSheet->getData(), true);
            $categoryData    = $allData['category'];
            $setCode = 'icecat-'. $categoryData['id'];

            // call transformer if not already transformed
            if (!in_array($setCode, $setsCode)) {
                $transformer = new DataSheetArrayToSetTransformer($productManager, $dataSheet);
                $set = $transformer->transform();

                $productManager->getPersistenceManager()->persist($set);
                $setsCode[] = $set->getCode();
            }
        }

        if ($flush) {
            // flush content
            $productManager->getPersistenceManager()->flush();
        }
    }

}
