<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Command;

use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;

use Pim\Bundle\ConnectorIcecatBundle\Command\ImportBaseProductsCommand;

/**
 * Test for commands
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ImportBaseProductsCommandTest extends CommandTestCase
{
    /**
     * test file name
     * @var string
     */
    const FILENAME = 'dl-icecat-base-products.txt';

    /**
     * Path for test file from this class directory
     * @var string
     */
    const FILEPATH = '/../../DataFixtures/Tests/Files/';

    /**
     * Test import data
     */
    /*public function testImportData()
    {
        // import data
        $command = new ImportBaseProductsCommand();
        $command->importData(dirname(__FILE__) . self::FILEPATH . self::FILENAME);
    }*/

    /**
     * Test launch method
     */
    /*public function testExecute()
    {
        // TODO : until we fix the problem

        $client = self::createClient();

        $output = $this->runCommand($client, "connectoricecat:importBaseProducts");

        echo $output;
    }*/
}