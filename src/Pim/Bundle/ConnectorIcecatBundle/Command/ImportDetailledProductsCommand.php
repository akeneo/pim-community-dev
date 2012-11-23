<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;

use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;

use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductArrayToCatalogProductTransformer;

use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductXmlToArrayTransformer;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpReader;

use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpDownload;

// use Pim\Bundle\ConnectorIcecatBundle\PimConnectorIcecatBundle;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pim\Bundle\ConnectorIcecatBundle\Document\ProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;

// use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Doctrine\ODM\MongoDB\Query\Builder;
/**
 * Import detailled data for asked products
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportDetailledProductsCommand extends AbstractPimCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('connectoricecat:importDetailledProducts')
            ->setDescription('Import detailled data for a set of products')
            ->addArgument(
                'limit',
                InputArgument::REQUIRED,
                'Number of products to be imported'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get arguments
        $limit = $input->getArgument('limit');
        $startLimit = $limit;

        // get config
        $configManager = $this->getConfigManager();
        $login       = $configManager->getValue(Config::LOGIN);
        $password    = $configManager->getValue(Config::PASSWORD);
        $baseDir     = $configManager->getValue(Config::BASE_DIR);
        $downloadUrl = $configManager->getValue(Config::BASE_URL) . $configManager->getValue(Config::BASE_PRODUCTS_URL);
        $baseFilePath= 'http://data.icecat.biz/export/freexml.int/INT/';

        // get data objects
        $dm = $this->getDocumentManager();

        $products = $dm->getRepository('PimConnectorIcecatBundle:ProductDataSheet')->findBy(array('isImported' => 0));

        echo count($products).PHP_EOL;

        // prepare objects
        $reader = new FileHttpReader();

        // loop on products
        $batchSize = 0;

        TimeHelper::addValue('load-product');
        MemoryHelper::addValue('load-product');
        foreach ($products as $product) {

            // get xml content
            $file = $product->getProductId() .'.xml';
            $content = $reader->process($baseFilePath . $file, $login, $password, false);
            $content = simplexml_load_string($content);

            // keep only used data, convert to array and encode ton json format
            $xmlToArray = new ProductXmlToArrayTransformer($content);
            $xmlToArray->transform();
            $baseData = $xmlToArray->getProductBaseData();
            $features = $xmlToArray->getProductFeatures();
            $data= array('basedata' => $baseData, 'features' => $features);

            // persist details
            $product->setXmlDetailledData(json_encode($data));
            $product->setIsImported(1);
            $dm->persist($product);
            echo 'insert '.$product->getProductId().PHP_EOL;

            // save by batch of x product details
            if (++$batchSize % 10 === 0) {
                $dm->flush();
                $output->writeln('Batch size : '. $batchSize);
                $output->writeln('memory usage -> '. $this->getMemoryUsage());
                $dm->clear();
                $output->writeln('after clear memory usage -> '. $this->getMemoryUsage());
                gc_collect_cycles();
                $output->writeln('after gc_collect_cycles -> '. $this->getMemoryUsage());
            }

            // stop when limit is attempted
            if (--$limit === 0) {
                $dm->flush();
                break;
            }
            $this->writeln('Load product : '. TimeHelper::writeGap('load-product') .' - '. MemoryHelper::writeGap('load-product'));
        }
    }

    /**
     * @return Doctrine\ODM\MongoDB\Query\Builder;
     */
    protected function createQB()
    {
        return $this->getDocumentManager()
                    ->getRepository('PimConnectorIcecatBundle:ProductDataSheet')
                    ->createQueryBuilder();
    }

    /**
     * @return ProductManager
     */
    protected function getProductManager()
    {
        return $this->getContainer()->get('pim.catalog.product_manager');
    }
}