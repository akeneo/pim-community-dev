<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpDownload;
use Pim\Bundle\DataFlowBundle\Model\Extract\FileUnzip;

use Pim\Bundle\ConnectorIcecatBundle\Document\ProductDataSheet;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;
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
        $filePath         = $baseDir . $configManager->getValue(Config::PRODUCT_FILE);

        // download source
        $this->downloadFile($downloadUrl, $archivedFilePath);

        // unpack source
        $this->unpackFile($archivedFilePath, $filePath);

        // import products
        if (($handle = fopen($filePath, 'r')) !== false) {
            $this->batchSize = 0;

            // not parse header
            fgetcsv($handle, 1000, "\t");
            while (($data = fgetcsv($handle, 1000, "\t")) !== false) {
                $this->createProduct($data);
            }
        }

        // persist documents with constraint validation
        $this->getDocumentManager()->flush();

        $this->writeln('command executed successfully');
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
        $downloader = new FileHttpDownload();
        $downloader->process($downloadUrl, $archivedFilePath, $login, $password, false);
    }

    /**
     * Unpack downloaded file
     * @param string $archivedFilePath path for local archived file
     * @param string $filePath         path for local unpacked file
     */
    protected function unpackFile($archivedFilePath, $filePath)
    {
        $unpacker = new FileUnzip();
        $unpacker->process($archivedFilePath, $filePath, false);
    }

    /**
     * Create a product document data sheet from an array representing a line of the source file
     * @param array $data
     */
    protected function createProduct($data)
    {
        // instanciate new object
        $product = new ProductDataSheet();
        $product->setProductId($data[0]);
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
        $this->getDocumentManager()->flush();
        $this->writeln('Batch size : '. $this->batchSize);
        $this->writeln('memory usage -> '. $this->getMemoryUsage());
        $this->getDocumentManager()->clear();
        $this->writeln('after clear memory usage -> '. $this->getMemoryUsage());
    }

    /**
     * Get memory usage in
     * @return number
     */
    private function getMemoryUsage()
    {
        $size = memory_get_usage(true);

        return $size / 1024 / 1024;
    }
}
