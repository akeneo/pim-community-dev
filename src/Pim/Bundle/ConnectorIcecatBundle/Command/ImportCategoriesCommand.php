<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Import all categories from icecat
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportCategoriesCommand extends AbstractPimCommand
{
    /**
     * List of categories
     * @var array
     */
    protected $categories = array();

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('connectoricecat:importCategories')
             ->setDescription('Import categories from icecat to localhost database');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $srvConnector = $this->getConnectorService();
        $srvConnector->importIcecatCategories();
    }
}
