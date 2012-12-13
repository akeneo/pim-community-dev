<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Import detailled data for asked products
 *
 * Launch with command :
 *     php app/console connectoricecat:importDataSheetsDetail [limit]
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportDataSheetsDetailCommand extends AbstractPimCommand
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
        $this->setName('connectoricecat:importDataSheetsDetail')
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
        // run detailled product import
        $srvConnector = $this->getConnectorService();
        $srvConnector->importIcecatDetailledProducts($this->limit);
    }

}