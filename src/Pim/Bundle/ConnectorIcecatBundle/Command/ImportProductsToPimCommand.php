<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;

use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pim\Bundle\ConnectorIcecatBundle\Document\ProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;

use Pim\Bundle\ConnectorIcecatBundle\Transform\DataSheetArrayToProductTransformer;

use Doctrine\ODM\MongoDB\Query\Builder;

/**
 *
 * Mass import from icecat product to pim
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ImportProductsToPimCommand extends AbstractPimCommand
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
        $this->setName('connectoricecat:importProductsToPim')
        ->setDescription('Import detailled data to product pim')
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
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get products
        $sheets = $this->getProductsDataSheet();
        $this->writeln($sheets->count() .' products found'.PHP_EOL);

        // loop on products
        $this->batchSize = 0;

        TimeHelper::addValue('start-import');
        TimeHelper::addValue('loop-import');
        MemoryHelper::addValue('memory');

        $productManager = $this->getContainer()->get('pim.catalog.product_manager');


        foreach ($sheets as $sheet) {

            $transformer = new DataSheetArrayToProductTransformer($productManager, $sheet);

            $product = $transformer->transform();
            /*
            $data = json_decode($sheet->getXmlDetailledData());

            var_dump($data);*/
            exit();



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
        $this->writeln('total time elapsed : '. TimeHelper::writeGap('start-import'));
    }

    /**
     * Get all product data sheet
     * @return ProductDataSheet
     */
    protected function getProductsDataSheet()
    {
        $products = $this->getDocumentManager()
            ->getRepository('PimConnectorIcecatBundle:ProductDataSheet')
            ->findBy(array('isImported' => 1));

        return $products;
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
