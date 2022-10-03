<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Authentication\Sso\Log;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\DbTableLogHandler;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\EnabledConfigurationRepository;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Monolog\Logger;

final class DbTableLogHandlerIntegration extends TestCase
{
    public function testItInsertsLogEntries(): void
    {
        $configRepo = new EnabledConfigurationRepository();

        $handler = new DbTableLogHandler($configRepo, $this->getConnection());

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
        $configRepo = new EnabledConfigurationRepository();

        $logger = new Logger('my_test_logger');

        $handler = new DbTableLogHandler($configRepo, $this->getConnection());

        $logger->pushHandler($handler);

        $logger->info('My first log message');
        $logger->warning('My second log message');
        $logger->error('My third log message');
        $logger->critical('My fourth log message');

        $this->assertLogTableRowCount(4);
    }


    private function assertLogTableIsSame(array $expected): void
    {
        $connection = $this->getConnection();

        $actual = $connection->fetchAllAssociative(
            sprintf(
                'SELECT channel, level, message, time FROM %s ORDER BY time ASC',
                DbTableLogHandler::TABLE_NAME
            )
        );

        $this->assertEquals($expected, $actual);
    }

    private function assertLogTableRowCount(int $expectedCount): void
    {
        $connection = $this->getConnection();

        $actualCount = $connection->fetchOne(
            sprintf(
                'SELECT COUNT(*) FROM %s',
                DbTableLogHandler::TABLE_NAME
            )
        );

        $this->assertEquals($expectedCount, $actualCount);
    }

    protected function tearDown(): void
    {
        $connection = $this->getConnection();
        if ($connection->createSchemaManager()->tablesExist([DbTableLogHandler::TABLE_NAME]) == true) {
            $connection->executeQuery(sprintf('TRUNCATE TABLE %s', DbTableLogHandler::TABLE_NAME));
        }
    }

    protected function getConfiguration()
    {
        return null;
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
