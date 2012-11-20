<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileUnzip;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpDownload;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;

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
class DownloadBaseProductsCommand extends ContainerAwareCommand
{
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('connectoricecat:downloadBaseProducts')
            ->setDescription('Import product data sheet in Mongo DB');
    }
    
    protected function closeFile()
    {
        fclose($this->newHandle);
//         $this->createTask();
    }
    
    protected function createTask()
    {
//         $task = new Task();
//         $task->setName('DownloadBaseProducts');
//         $task->setBundleName('ConnectorIcecat');
//         $task->set
    }
    
    protected function openFile()
    {
        $this->newHandle = fopen('/tmp/base-products-');
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get config
        $configManager = $this->getConfigManager();
        $login = $configManager->getValue(Config::LOGIN);
        $password = $configManager->getValue(Config::PASSWORD);
        $downloadUrl = /*$configManager->getValue(Config::BASE_URL) .*/ $configManager->getValue(Config::PRODUCTS_URL);
        $archivedFilePath = '/tmp/'. $configManager->getValue(Config::PRODUCTS_ARCHIVED_FILE);
        $filePath = '/tmp/base-products-complete.csv';

        // get xml content
        $fileReader = new FileHttpDownload();
        $fileReader->process($downloadUrl, $archivedFilePath, $login, $password, false);
        
        // unpack source
        $unpacker = new FileUnzip();
        $unpacker->process($archivedFilePath, $filePath, false);
        
        // get document manager
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        
        // cut file in differents part files
        if (($handle = fopen($filePath, 'r')) !== false) {
            $fileSize = 0;
            $countFile = 0;
            
            $headers = fgets($handle);
            
            $newHandle = fopen('/tmp/base-products-'. ++$countFile .'.csv', 'a+');
            fwrite($newHandle, $headers);
            
            while (!feof($handle)) {
                $line = fgets($handle);
                
                fwrite($newHandle, $line);
                // one file -> 100k products
                if (++$fileSize % 100000 === 0) {
                    fclose($newHandle);
                    $newHandle = fopen('/tmp/base-products-'. ++$countFile .'.csv', 'a+');
                    fwrite($newHandle, $headers);
                }
            }
            
            fclose($newHandle);
            fclose($handle);
        }
        
        $output->writeln('command executed successfully');
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager()
    {
        return $this->getContainer()->get('pim.connector.icecat.configmanager');
    }
}