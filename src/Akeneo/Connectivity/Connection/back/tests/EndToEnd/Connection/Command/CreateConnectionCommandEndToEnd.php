<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Connection\Command;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\CommandTestCase;
use Akeneo\Test\Integration\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateConnectionCommandEndToEnd extends CommandTestCase
{
    private Command $command;

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
        $this->assertMatchesRegularExpression('/^A new connection has been created with the following settings:\n/', $output);
        $this->assertMatchesRegularExpression('/Secret: [A-Za-z0-9]*\n/', $output);
        $this->assertMatchesRegularExpression('/Secret: [A-Za-z0-9]*\n/', $output);
        $this->assertMatchesRegularExpression('/Client ID: [A-Za-z0-9_]*\n/', $output);
        $this->assertMatchesRegularExpression('/Username: magento_[0-9]{4}\n/', $output);
        $this->assertMatchesRegularExpression('/Password: [A-Za-z0-9]*\n/', $output);
        $this->assertMatchesRegularExpression('/Auditable: (yes|no)\n/', $output);
        $this->assertStringContainsString('Code: magento', $output);
    }

    public function test_it_fails_to_create_a_connection_with_a_too_short_code(): void
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'code' => 'a',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Connection code is too short. It should have 3 characters or more.', $output);
    }

    public function test_it_fails_to_create_a_connection_with_a_wrong_user_group(): void
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'code' => 'magento',
            '--user-group' => 'wrong_user_group',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('The user group was not found. Make sure the specified user group exists.', $output);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
