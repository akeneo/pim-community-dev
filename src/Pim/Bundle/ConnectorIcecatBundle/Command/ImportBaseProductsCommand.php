<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpDownload;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;

use Pim\Bundle\ConnectorIcecatBundle\Document\ProductDataSheetDocument;
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
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('connectoricecat:importBaseProducts')
            ->setDescription('Import product data sheet in Mongo DB');
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
        $downloadUrl = $configManager->getValue(Config::BASE_URL) . $configManager->getValue(Config::BASE_PRODUCTS_URL);

        // get xml content
        $fileReader = new FileHttpReader();
        $fileReader = new FileHttpDownload();
        $content = $fileReader->process($downloadUrl, '/tmp/icecat-base-products.xml', $login, $password);

        // get document manager
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        // read xlk cibtebt
        libxml_use_internal_errors(true);
        $xmlContent = simplexml_load_string($content);

        foreach ($xmlContent->xpath('//file') as $file) {
            // Instanciate new object
            $doc = new ProductDataSheetDocument();
            $doc->setImportPath($file['path']->asXML());
            $doc->setProductId($file['Product_ID']);
            $doc->setXmlBaseData($file->asXML());

            $dm->persist($doc);
        }

        // persist documents with constraint validation
        $dm->flush();
        
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
