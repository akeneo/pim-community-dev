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
 * Aims to insert a collection of attributes from a collection of IcecatProductDataSheet
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AttributesFromDataSheetsWriter
{
    /**
     * Import a collection of attributes from datasheets
     *
     * @param ProductManager $productManager product manager
     * @param array          $dataSheets     array of product datasheet
     * @param boolean        $flush          true to flush
     */
    public function import(ProductManager $productManager, $dataSheets, $flush)
    {
        $attributesCode = array();

        // Call transformer for each datasheet
        foreach ($dataSheets as $dataSheet) {
            $transformer = new DataSheetArrayToAttributesTransformer($productManager, $dataSheet);
            $attributes = $transformer->transform();

            foreach ($attributes as $attribute) {
                if (!in_array($attribute->getCode(), $attributesCode)) {
                    $productManager->getPersistenceManager()->persist($attribute);
                    $attributesCode[] = $attribute->getCode();
                }
            }
        }

        if ($flush) {
            // flush content
            $productManager->getPersistenceManager()->flush();
        }
    }
}
