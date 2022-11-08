<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Authentication\Sso\Log;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\DbTableLogHandler;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\EnabledConfigurationRepository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\Test\Integration\TestCase;
use Monolog\Logger;

final class DbTableLogHandlerIntegration extends TestCase
{
    public function testItInsertsLogEntries(): void
    {
        $connection = $this->get('database_connection');
        $configRepo = new EnabledConfigurationRepository();

        $handler = new DbTableLogHandler($configRepo, $connection);

        $handler->handle(
            [
                'message' => 'My nice message',
                'context' => [],
                'level' =>  Logger::DEBUG,
                'level_name' => Logger::getLevelName(Logger::DEBUG),
                'channel' => 'authentication',
                'datetime' => new \DateTime('2013-01-01 13:43:25 GMT+2'),
                'extra' => array()
            ]
        );

        $this->assertLogTableIsSame(
            [
                [
                    'channel' => 'authentication',
                    'level' =>  Logger::DEBUG,
                    'message' => "[2013-01-01 13:43:25] authentication.DEBUG: My nice message [] []\n",
                    'time' => '2013-01-01 11:43:25'
                ]
            ]
        );
    }

    public function testItIntegratesWithMonolog()
    {
        $connection = $this->get('database_connection');
        $configRepo = new EnabledConfigurationRepository();

        $logger = new Logger('my_test_logger');

        $handler = new DbTableLogHandler($configRepo, $connection);

        $logger->pushHandler($handler);

        $logger->info('My first log message');
        $logger->warning('My second log message');
        $logger->error('My third log message');
        $logger->critical('My fourth log message');

        $this->assertLogTableRowCount(4);
    }


    private function assertLogTableIsSame(array $expected): void
    {
        $connection =  $this->get('database_connection');

        $actual = $connection->fetchAll(
            sprintf(
                'SELECT channel, level, message, time FROM %s ORDER BY time ASC',
                DbTableLogHandler::TABLE_NAME
            )
        );

        $this->assertEquals($expected, $actual);
    }

    private function assertLogTableRowCount(int $expectedCount): void
    {
        $connection =  $this->get('database_connection');

        $actualCount = $connection->fetchColumn(
            sprintf(
                'SELECT COUNT(*) FROM %s',
                DbTableLogHandler::TABLE_NAME
            ),
            [],
            0
        );

        $this->assertEquals($expectedCount, $actualCount);
    }

    protected function tearDown(): void
    {
        $connection = $this->get('database_connection');
        if ($connection->getSchemaManager()->tablesExist([DbTableLogHandler::TABLE_NAME]) == true) {
            $connection->executeQuery(sprintf('TRUNCATE TABLE %s', DbTableLogHandler::TABLE_NAME));
        }
    }

    protected function getConfiguration()
    {
        return null;
    }
}
