<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\CommandTestCase;
use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Test\Integration\Configuration;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateConnectionCommandEndToEnd extends CommandTestCase
{
    /** @var Command */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = $this->application->find('akeneo:connectivity-connection:create');
    }

    public function test_it_creates_a_connection(): void
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'code' => 'magento',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertRegExp('/^A new connection has been created with the following settings:\n/', $output);
        $this->assertRegExp('/Secret: [A-Za-z0-9]*\n/', $output);
        $this->assertRegExp('/Client ID: [A-Za-z0-9_]*\n/', $output);
        $this->assertRegExp('/Username: magento_[0-9]{4}\n/', $output);
        $this->assertRegExp('/Password: [A-Za-z0-9]*\n/', $output);
        $this->assertStringContainsString('Code: magento', $output);
    }

    public function test_it_fails_to_create_a_connection_with_a_too_short_code(): void
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'code' => 'a',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('akeneo_connectivity.connection.connection.constraint.code.too_short', $output);
        $this->assertStringContainsString('akeneo_connectivity.connection.connection.constraint.label.too_short', $output);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
