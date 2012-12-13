<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Import whole set of basic data products from icecat
 *
 * Launch with command :
 *     php app/console connectoricecat:importDataSheetsBase
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportDataSheetsBaseCommand extends AbstractPimCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('connectoricecat:importDataSheetsBase')
             ->setDescription('Import icecat base product data sheet');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // run base product import
        $srvConnector = $this->getConnectorService();
        $srvConnector->importIcecatBaseProducts();
    }

}
