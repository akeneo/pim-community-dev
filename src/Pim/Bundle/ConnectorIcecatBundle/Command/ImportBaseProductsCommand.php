<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Pim\Bundle\ConnectorIcecatBundle\Document\ProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;
use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;
use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductCsvClean;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpDownload;
use Pim\Bundle\DataFlowBundle\Model\Extract\FileUnzip;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Import whole set of basic data products from icecat
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportBaseProductsCommand extends AbstractPimCommand
{

    /**
     * Actual counter for inserting loop
     * @var integer
     */
    protected $batchSize;

    /**
     * Max counter for inserting loop. When batch size achieve this value, manager make a flush/clean
     * @staticvar integer
     */
    protected static $maxBatchSize = 2000;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('connectoricecat:importBaseProducts')
            ->setDescription('Import product data sheet in Mongo DB');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get config
        $configManager    = $this->getConfigManager();
        $downloadUrl      = $this->getConfigManager()->getValue(Config::PRODUCTS_URL);
        $baseDir          = $configManager->getValue(Config::BASE_DIR);
        $archivedFilePath = $baseDir . $configManager->getValue(Config::PRODUCTS_ARCHIVED_FILE);
        $filePath         = $baseDir . $configManager->getValue(Config::PRODUCTS_FILE);

        // download source
        $this->downloadFile($downloadUrl, $archivedFilePath);

        // unpack source
        $this->unpackFile($archivedFilePath, $filePath);

        // import products
        TimeHelper::addValue('import-base');
        $this->importData($filePath);

        // persist documents with constraint validation
        $this->writeln('command executed successfully : '. TimeHelper::writeGap('import-base'));
    }

    /**
     * Import data from file to local database
     * @param string $filePath
     */
    public function importData($filePath)
    {
        TimeHelper::addValue('full-import');
        MemoryHelper::addValue('memory');

        if (($handle = fopen($filePath, 'r')) !== false) {
            $this->batchSize = 0;

            // not parse header
            fgetcsv($handle, 1000, "\t");

            // parse rows
            $indBatch = 1;
            TimeHelper::addValue('loop-import');
            while (($data = fgetcsv($handle, 1000, "\t")) !== false) {
                $productId = (integer) $data[0];

                $product = new ProductDataSheet();
                $product->setProductId($productId);
                $product->setIsImported(0);

                // persist object and flush if necessary
                $this->getDocumentManager()->persist($product);

                if (++$this->batchSize === self::$maxBatchSize) {

                    $this->getDocumentManager()->flush(/*null, array('safe' => false)*/); // not respect index constraint
                    $this->getDocumentManager()->clear();
                    $this->writeln('After flush range '.($indBatch++).' -> '. MemoryHelper::writeGap('memory').' '. TimeHelper::writeGap('loop-import'));
                    $this->batchSize = 0;

                    // TODO for test
                   // break;
                }
            }

            // last range flush
            $this->getDocumentManager()->flush();

            fclose($handle);
        }

        $this->removeDuplicate();

        $this->writeln('Import -> '. TimeHelper::writeGap('full-import'));
    }

    /**
     * Download remote file
     * @param string $downloadUrl      url of the file on remote domain
     * @param string $archivedFilePath path for local archived file
     */
    protected function downloadFile($downloadUrl, $archivedFilePath)
    {
        // get config for optional options
        $login            = $this->getConfigManager()->getValue(Config::LOGIN);
        $password         = $this->getConfigManager()->getValue(Config::PASSWORD);

        // download xml file
        TimeHelper::addValue('download-file');
        $downloader = new FileHttpDownload();
        $downloader->process($downloadUrl, $archivedFilePath, $login, $password, false);
        $this->writeln('Download File -> '. TimeHelper::writeGap('download-file'));
    }

    /**
     * Unpack downloaded file
     * @param string $archivedFilePath path for local archived file
     * @param string $filePath         path for local unpacked file
     */
    protected function unpackFile($archivedFilePath, $filePath)
    {
        TimeHelper::addValue('unpack');
        MemoryHelper::addValue('unpack');
        $unpacker = new FileUnzip();
        $unpacker->process($archivedFilePath, $filePath, false);
        $this->writeln('Unpack File -> '. TimeHelper::writeGap('unpack') .' - '. MemoryHelper::writeGap('unpack'));
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
