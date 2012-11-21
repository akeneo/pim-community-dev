<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;

use Pim\Bundle\ConnectorIcecatBundle\Command\ImportBaseProductsCommand;
/**
 * Test for commands
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ImportBaseProductsCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test launch method
     */
    public function testExecute()
    {
        $app = new Application();
        $app->add(new ImportBaseProductsCommand());

        $command = $app->find('connectoricecat:importBaseProducts');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertTrue(true);
    }
}