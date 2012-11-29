<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;

use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;

use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductArrayToCatalogProductTransformer;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\ProductIntXmlToArrayTransformer;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpReader;

use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpDownload;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pim\Bundle\ConnectorIcecatBundle\Document\ProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;

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
     * Actual counter for inserting loop
     * @var integer
     */
    protected $batchSize;

    /**
     * Max counter for inserting loop. When batch size achieve this value, manager make a flush/clean
     * @staticvar integer
     */
    protected static $maxBatchSize = 100;

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
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        // get arguments
        $this->limit = $input->getArgument('limit');

        // get config and initialize base file path
        $configManager = $this->getConfigManager();
        $login         = $configManager->getValue(Config::LOGIN);
        $password      = $configManager->getValue(Config::PASSWORD);
        $baseUrl       = $configManager->getValue(Config::BASE_URL);
        $productUrl    = $configManager->getValue(Config::BASE_PRODUCTS_URL);
        $this->baseFilePath = $baseUrl . $productUrl;

        // initialize reader
        $this->reader = new FileHttpReader();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get config
        $configManager = $this->getConfigManager();
        $login         = $configManager->getValue(Config::LOGIN);
        $password      = $configManager->getValue(Config::PASSWORD);

        // get products
        $products = $this->getProductsDataSheet();
        $this->writeln(count($products) .' products found'.PHP_EOL);

        // loop on products
        $this->batchSize = 0;

        TimeHelper::addValue('start-import');
        TimeHelper::addValue('loop-import');
        MemoryHelper::addValue('memory');

        foreach ($products as $product) {
            try {
                // get xml content
                $file = $product->getProductId() .'.xml';
                $content = $this->reader->process($this->baseFilePath . $file, $login, $password);
                $content = simplexml_load_string($content);

                if (!$content) {
                    $this->writeln('Exception -> '. $file . ' is not well formed');
                    $product->setIsImported(-1);
                    $this->getDocumentManager()->persist($product);

                } else {
                    // keep only used data, convert to array and encode ton json format
                    $xmlToArray = new ProductIntXmlToArrayTransformer($content);
                    $data = $xmlToArray->transform();

                    // persist details
                    $product->setXmlDetailledData(json_encode($data));
                    $product->setIsImported(1);
                    $this->getDocumentManager()->persist($product);
                    //$this->writeln('insert '. $product->getProductId());

                    // save by batch of x product details
                    if (++$this->batchSize === self::$maxBatchSize) {
                        $this->flush();

                        $this->writeln('After flush range of '.self::$maxBatchSize.' '. MemoryHelper::writeGap('memory').' '. TimeHelper::writeGap('loop-import'));
                        $this->batchSize = 0;
                    }

                    // stop when limit is attempted
                    // TODO : must be remove when query with where clause and limit work
                    if (--$this->limit === 0) {
                        $this->flush();
                        break;
                    }
                }
            } catch (\Exception $e) {
                $this->writeln('Exception -> '. $e->getMessage());
                $product->setIsImported(-1);
                $this->getDocumentManager()->persist($product);
            }
        }
        $this->writeln('total time elapsed : '. TimeHelper::writeGap('start-import'));
    }

    /**
     * Get all product data sheet
     * @return ProductDataSheet
     */
    protected function getProductsDataSheet()
    {
        return $this->getDocumentManager()
                    ->getRepository('PimConnectorIcecatBundle:ProductDataSheet')
                    ->findBy(array('isImported' => 0));
    }

    /**
     * Call document manager to flush data
     */
    protected function flush()
    {
        $this->getDocumentManager()->flush();
        $this->getDocumentManager()->clear();
    }
}