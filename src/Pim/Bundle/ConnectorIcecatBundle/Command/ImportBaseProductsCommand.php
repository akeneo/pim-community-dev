<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileUnzip;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpDownload;

use Pim\Bundle\ConnectorIcecatBundle\Document\ProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Entity\ConfigManager;
use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpReader;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Import whole set of basic data products from icecat
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportBaseProductsCommand extends ContainerAwareCommand
{
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
        $login            = $configManager->getValue(Config::LOGIN);
        $password         = $configManager->getValue(Config::PASSWORD);
        $baseDir          = $configManager->getValue(Config::BASE_DIR);
        $downloadUrl      = $configManager->getValue(Config::PRODUCTS_URL);
        $archivedFilePath = $baseDir . $configManager->getValue(Config::PRODUCTS_ARCHIVED_FILE);
        $filePath         = $baseDir . $configManager->getValue(Config::PRODUCT_FILE);

        // get xml content
        $fileReader = new FileHttpDownload();
        $fileReader->process($downloadUrl, $archivedFilePath, $login, $password, false);

        // unpack source
        $unpacker = new FileUnzip();
        $unpacker->process($archivedFilePath, $filePath, false);

        // get document manager
        $dm = $this->getDocumentManager();

        // import products
        if (($handle = fopen($filePath, 'r')) !== false) {
            $batchSize = 0;

            // not parse header
            $headers = fgetcsv($handle, 1000, "\t");
            while (($data = fgetcsv($handle, 1000, "\t")) !== false) {
                // instanciate new object
                $product = new ProductDataSheet();
                $product->setProductId($data[0]);
                $product->setIsImported(0);

                $dm->persist($product);
                if (++$batchSize % 20000 === 0) {
                    $dm->flush();
                    $output->writeln('Batch size : '. $batchSize);
                    $output->writeln('memory usage -> '. $this->getMemoryUsage());
                    $dm->clear();
                    $output->writeln('after clear memory usage -> '. $this->getMemoryUsage());
                    gc_collect_cycles();
                    $output->writeln('after gc_collect_cycles -> '. $this->getMemoryUsage());
                }
            }
        }

        // persist documents with constraint validation
        $dm->flush();

        $output->writeln('command executed successfully');
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

    /**
     * @return ConfigManager
     */
    protected function getConfigManager()
    {
        return $this->getContainer()->get('pim.connector.icecat.configmanager');
    }

    /**
     * @return DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
    }
}
