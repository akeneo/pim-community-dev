<?php

namespace Akeneo\Tool\Bundle\ApiBundle\tests\integration\Command;

use Akeneo\Tool\Bundle\ApiBundle\Command\CreateClientCommand;
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

        $command = $application->find('pim:oauth-server:create-client');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command'  => $command->getName(),
            'label' => 'Magento connector',
        ]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('A new client has been added.', $output);
        $this->assertStringContainsString('client_id:', $output);
        $this->assertStringContainsString('secret:', $output);
        $this->assertStringContainsString('label: Magento connector', $output);

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testResponseWhenMissingLabel()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "label").');

        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('pim:oauth-server:create-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
    }
}
