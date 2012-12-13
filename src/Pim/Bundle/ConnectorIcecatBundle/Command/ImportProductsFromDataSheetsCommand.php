<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Command which import products from data sheets
 *
 * Launch with command :
 *     php app/console connectoricecat:importProductsFromDataSheets [limit]
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ImportProductsFromDataSheetsCommand extends AbstractPimCommand
{
    /**
     * Limit of detailled products imported with this command
     * @var integer
     */
    protected $limit;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('connectoricecat:importProductsFromDataSheets')
             ->setDescription('Import products from data sheets')
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
        // run data sheets to product import
        $srvConnector = $this->getConnectorService();
        $srvConnector->importProductsFromDataSheet($this->limit);
    }
}
