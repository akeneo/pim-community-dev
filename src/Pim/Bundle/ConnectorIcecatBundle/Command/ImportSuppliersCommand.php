<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\Category;

use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;
use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\CategoriesXmlToCategoriesTransformer;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpDownload;
use Pim\Bundle\DataFlowBundle\Model\Extract\FileUnzip;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Import all suppliers from icecat
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportSuppliersCommand extends AbstractPimCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('connectoricecat:importSuppliers')
            ->setDescription('Import suppliers from icecat to localhost database');
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
        $srvConnector->importIcecatSuppliers();

        $this->writeln('total time elapsed : '. TimeHelper::writeGap('start-import'));
    }
}
