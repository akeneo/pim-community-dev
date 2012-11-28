<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Pim\Bundle\ConnectorIcecatBundle\Document\ProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;
use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;

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
    protected static $maxBatchSize = 20000;

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
        $this->flush();
        $this->writeln('command executed successfully : '. TimeHelper::writeGap('import-base'));
    }

    /**
     * Import data from file to local database
     * @param string $filePath
     */
    public function importData($filePath)
    {
        if (($handle = fopen($filePath, 'r')) !== false) {
            $this->batchSize = 0;

            // not parse header
            fgetcsv($handle, 1000, "\t");
            while (($data = fgetcsv($handle, 1000, "\t")) !== false) {
                $this->createProduct((integer) $data[0]);
            }

            fclose($handle);
        }
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
     * Create a product document data sheet from icecat product id
     * @param integer $productId
     */
    protected function createProduct($productId)
    {
        // instanciate a new object
        $product = new ProductDataSheet();
        $product->setProductId($productId);
        $product->setIsImported(0);

        // persist object and flush if necessary
        $this->getDocumentManager()->persist($product);
        if (++$this->batchSize === self::$maxBatchSize) {
            $this->flush();
            $this->batchSize = 0;
        }
    }

    /**
     * Call document manager to flush data
     */
    protected function flush()
    {
        $this->writeln('Before clear -> '. MemoryHelper::writeValue('memory'));
        // TODO : Change try/catch by removing document from unit of work
//         try {
            $this->getDocumentManager()->flush();
//         } catch (\MongoCursorException $e) {
//             $this->writeln('MongoCursorException');
//             $this->writeln($e->getCode() .' : '. $e->getMessage());
//         } catch (\Exception $e) {
//             throw $e;
//         }
        $this->getDocumentManager()->clear();
        $this->writeln('After clear   -> '. MemoryHelper::writeGap('memory'));
    }
}
