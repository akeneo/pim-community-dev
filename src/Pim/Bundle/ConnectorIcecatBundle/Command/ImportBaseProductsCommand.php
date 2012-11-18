<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Pim\Bundle\ConnectorIcecatBundle\Document\ProductDataSheetDocument;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpReader;

use Pim\Bundle\ConnectorIcecatBundle\Entity\ConfigManager;
use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ImportBaseProductsCommand extends ContainerAwareCommand
{
    /**
     *
     * @var string
     */
    protected $content;

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
        $content = $fileReader->process($downloadUrl, $login, $password);

        var_dump($content);

        // get document manager
        $om = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        // read xlk cibtebt
//         libxml_use_internal_errors(true);
//         $xmlContent = simplexml_load_string($content);

//         foreach ($xmlContent->xpath('//file') as $file) {
//             $doc = new ProductDataSheetDocument();
//             $doc->setImportPath($file['path']->asXML());
//             $doc->setIsImported(false); // TODO : Must be default value
//             $doc->setProductId($file['Product_ID']);
//             $doc->setXmlBaseData($file->asXML());

//             $om->persist($doc);
//         }

//         $om->flush();

        $output->writeln(strlen($content));
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager()
    {
        return $this->getContainer()->get('pim.connector.icecat.configmanager');
    }
}
