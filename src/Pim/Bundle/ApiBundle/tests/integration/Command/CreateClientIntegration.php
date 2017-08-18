<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Command;

use Pim\Bundle\ApiBundle\Command\CreateClientCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

class CreateClientIntegration extends KernelTestCase
{
    public function testResponseWhenCreateClient()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->add(new CreateClientCommand());

        $command = $application->find('pim:oauth-server:create-client');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command'  => $command->getName(),
            'label' => 'Magento connector',
        ]);
        $output = $commandTester->getDisplay();

        $this->assertContains('A new client has been added.', $output);
        $this->assertContains('client_id:', $output);
        $this->assertContains('secret:', $output);
        $this->assertContains('label: Magento connector', $output);

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Not enough arguments (missing: "label").
     */
    public function testResponseWhenMissingLabel()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->add(new CreateClientCommand());

        $command = $application->find('pim:oauth-server:create-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
    }
}
