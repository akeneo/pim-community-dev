<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Symfony\Component\Console\Input\ArrayInput;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Command which import icecat product data
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ImportProductsCommand extends AbstractPimCommand
{
    /**
     * Number of detailled products imported
     * @staticvar integer
     */
    protected static $detailledImport = 10000;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('connectoricecat:importProducts')
             ->setDescription('Import products from Icecat to local Mongo Database');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // launch base product data importing
        $baseCommand = $this->getApplication()->find('connectoricecat:importBaseProducts');
        $returnCode = $baseCommand->run($input, $output);
        if ($returnCode === 0) {
            $this->writeln('base products successfully loaded');

            // launch detailled product data importing
            $detailledCommand = $this->getApplication()->find('connectoricecat:importDetailledProducts');
            // TODO : Use a fork manager to implements parallel command execution
            $inputArgs = array('limit' => self::$detailledImport);
            $returnCode = $detailledCommand->run(new ArrayInput($inputArgs), $output);

            if ($returnCode === 0) {
                $this->writeln(self::$detailledImport .' detailled products imported');
            }
        }
    }
}
