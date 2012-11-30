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
 * Import catalog product from detailled icecat datasheet
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ImportProductsCommand extends AbstractPimCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('connectoricecat:importProductsFromDataSheet')
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
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        TimeHelper::addValue('start-import');
        MemoryHelper::addValue('memory');

        // run detailled product import
        $srvConnector = $this->getContainer()->get('akeneo.connector.icecat_service');
        $srvConnector->importProductsFromDataSheet($this->limit);

        $this->writeln('total time elapsed : '. TimeHelper::writeGap('start-import'));
    }
}
