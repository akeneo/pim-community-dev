<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Write;

use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;

/**
 * Aims to insert base icecat product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseIcecatProductsFromCsvWriter
{

    /**
     * Import data from file to local database
     *
     * @param string        $filePath      file path
     * @param ObjectManager $objectManager manager
     * @param integer       $batchSize     batch size
     * @param boolean       $debug         true to display output
     */
    public function import($filePath, $objectManager, $batchSize = 2000, $debug = false)
    {
        MemoryHelper::addValue('memory');

        if (($handle = fopen($filePath, 'r')) !== false) {
            $nbProd = 0;

            // not parse header
            fgetcsv($handle, 1000, "\t");

            // parse rows
            $indBatch = 1;
            TimeHelper::addValue('loop-import');
            while (($data = fgetcsv($handle, 1000, "\t")) !== false) {

                $productId = (integer) $data[0];
                $supplierId = (integer) $data[4];

                $product = new IcecatProductDataSheet();
                $product->setProductId($productId);
                $product->setSupplierId($supplierId);
                $product->setStatus(IcecatProductDataSheet::STATUS_INIT);

                // persist object and flush if necessary
                $objectManager->persist($product);

                if (++$nbProd === $batchSize) {
                    $objectManager->flush(/*null, array('safe' => false)*/); // TODO not respect index constraint
                    $objectManager->clear();
                    echo /*$this->writeln(*/'After flush range '.($indBatch++).' -> '. MemoryHelper::writeGap('memory').' '. TimeHelper::writeGap('loop-import').PHP_EOL;//);
                    $nbProd = 0;
                }
            }

            // last range flush
            $objectManager->flush();

            fclose($handle);
        }

        //TODO $this->removeDuplicate();

    }

    /**
     * Remove duplicate product data sheets
     *
     * > db.ProductDataSheet.find().count()
     * 848000
     * > db.ProductDataSheet.distinct('productId').length;
     * 767099
     *
     * To remove duplicate :
     * > db.ProductDataSheet.dropIndex({"productId":1})
     * > db.ProductDataSheet.ensureIndex({productId:1}, {"unique":true, "dropDups":true})
     * @see http://docs.mongodb.org/manual/administration/indexes/#create-an-index
     */
    protected function removeDuplicate()
    {
        //        $schemaManager = $this->getDocumentManager()->getSchemaManager();

        /*
         [2012-11-28 22:16:02] doctrine.INFO: MongoDB query: {"ensureIndex":true,"keys":{"code":1},"options":{"unique":true,"sparse":false,"safe":true},"db":"akeneo_pim","collection":"ProductSet"} [] []
        [2012-11-28 22:16:07] doctrine.INFO: MongoDB query: {"command":true,"data":{"ensureIndex":"ProductDataSheet"},"options":[{"productId":1},{"unique":true,"dropDups":true}],"db":"akeneo_pim"} [] []


        $documentName = $this->getDocumentManager()->getClassMetadata('PimConnectorIcecatBundle:ProductDataSheet')->getName();
        $collection = $this->getDocumentManager()->getDocumentCollection($documentName);
        $collection->getDatabase()->command(array('ensureIndex' => $collection->getName()), array(array('productId' => 1), array('unique' => true, 'dropDups' => true)));
        */

    }

}
