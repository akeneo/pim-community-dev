<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;

use Pim\Bundle\ConnectorIcecatBundle\Command\ImportBaseProductsCommand;

use Symfony\Component\Console\Application;

class ImportBaseProductsCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new ImportBaseProductsCommand());
        
        $command = $app->find('connectoricecat:importBaseProducts');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));
        
        
    }
}