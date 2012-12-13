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
 * Command which import icecat product data
 *
 * Launch with command :
 *     php app/console connectoricecat:importProducts
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
        $this->setName('connectoricecat:importProducts')
             ->setDescription('Import products from data sheets');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // launch base product data importing
        $this->executeDataSheetsBaseCommand();

        // launch detailled product data importing
        $this->executeDataSheetsDetailCommand();

        // launch detailled data sheet to product importing
        $this->executeProductsFromDataSheetsCommand();
    }

    /**
     * Launch base product data sheet command importing
     * @throws \Exception
     */
    protected function executeDataSheetsBaseCommand()
    {
        $command = $this->getApplication()->find('connectoricecat:importDataSheetsBase');
        $returnCode = $command->run($this->input, $this->output);

        if ($returnCode !== 0) {
            throw new \Exception('error during base data sheets importing');
        }
    }

    /**
     * Launch detailled product data sheet command importing
     * @throws \Exception
     */
    protected function executeDataSheetsDetailCommand()
    {
//         $inputArgs = array('limit' => self::$detailledImport);
//         $returnCode = $detailledCommand->run(new ArrayInput($inputArgs), $output);


        $command = $this->getApplication()->find('connectoricecat:importDataSheetsDetail');
        $returnCode = $command->run($this->input, $this->output);

        if ($returnCode !== 0) {
            throw new \Exception('error during detailled data sheets importing');
        }
    }

    /**
     * Launch products from data sheet importing
     * @throws \Exception
     */
    protected function executeProductsFromDataSheetsCommand()
    {
        $command = $this->getApplication()->find('connectoricecat:importProductsFromDataSheets');
        $returnCode = $command->run($this->input, $this->output);

        if ($returnCode !== 0) {
            throw new \Exception('error during data sheets to products importing');
        }
    }
}
