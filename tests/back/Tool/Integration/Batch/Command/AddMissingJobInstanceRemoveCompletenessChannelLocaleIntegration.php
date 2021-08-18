<?php

declare(strict_types=1);

namespace Akeneo\Test\Integration\integration\BatchBundle\Command;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddMissingJobInstanceRemoveCompletenessChannelLocaleIntegration extends TestCase
{
    /** @var Connection */
    private $connection;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function jobInstanceExists(): bool
    {
        $sql = <<<SQL
          SELECT EXISTS(
              SELECT 1 FROM akeneo_batch_job_instance WHERE code = 'remove_completeness_for_channel_and_locale'
          ) as is_existing
SQL;
        $statement = $this->connection->executeQuery($sql);
        $platform = $this->connection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        return Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);
    }

    public function testCommandWhenJobInstanceAlreadyExist()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find('akeneo:batch:add-missing-job-instance-remove-completeness-for-channel-and-locale');
        $commandTester = new CommandTester($command);

        self::assertTrue($this->jobInstanceExists(), 'The job instance do exist before command execution');

        $commandResult = $commandTester->execute(['command' => $command->getName()]);

        self::assertTrue($this->jobInstanceExists(), 'The user group is not created');
        self::assertEquals(0, $commandResult, 'No error detected');
        self::assertEquals("The \"remove_completeness_for_channel_and_locale\" job instance already exists\n",
            $commandTester->getDisplay());
    }

    public function testCommandWhenJobInstanceDoNotExist()
    {
        $this->connection->executeQuery(
            'DELETE FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => 'remove_completeness_for_channel_and_locale']
        );

        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find('akeneo:batch:add-missing-job-instance-remove-completeness-for-channel-and-locale');
        $commandTester = new CommandTester($command);

        self::assertFalse($this->jobInstanceExists(), 'The job instance do not exist before command execution');

        $commandResult = $commandTester->execute(['command' => $command->getName()]);

        self::assertTrue($this->jobInstanceExists(), 'The user group is not created');
        self::assertEquals(0, $commandResult, 'No error detected');
        self::assertEquals("The \"remove_completeness_for_channel_and_locale\" job instance successfully added\n",
            $commandTester->getDisplay());
    }
}
