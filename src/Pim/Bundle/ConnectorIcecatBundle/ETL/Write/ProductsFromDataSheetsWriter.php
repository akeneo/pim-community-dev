<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Write;

use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;
use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\DataSheetArrayToProductTransformer;

/**
 * Aims to insert a collection of products from a collection of IcecatProductDataSheet
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductsFromDataSheetsWriter
{

    /**
     * Import product from datasheet
     *
     * @param ProductManager $productManager product manager
     * @param array          $dataSheets     array of product datasheet
     *
     * @return array
     */
    public function import(ProductManager $productManager, $dataSheets)
    {
        $productsCode = array();
        $productsError = array();

        // Call transformer for each datasheet
        foreach ($dataSheets as $dataSheet) {

            try {
                // verify if product code already in datasheet
                $allData = json_decode($dataSheet->getData(), true);
                $baseData = $allData['basedata'];
                $productCode = $baseData['id'];

                // call transformer if product not already transformed
                if (!in_array($productCode, $productsCode)) {
                    $transformer = new DataSheetArrayToProductTransformer($productManager, $dataSheet);
                    $product = $transformer->transform();

                    $productManager->getPersistenceManager()->persist($product);
                    $productsCode[] = $product->getSku();
                }
            } catch (\Exception $e) {
                $productsError[$dataSheet->getId()] = $e->getMessage();
            }
        }

        return $productsError;
    }

}
