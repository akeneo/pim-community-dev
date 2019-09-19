<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Authentication\Sso\Log;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\DbTableLogHandler;
use Akeneo\Test\Integration\TestCase;
use Monolog\Logger;

final class DbTableLogHandlerIntegration extends TestCase
{
    public function testItInsertsLogEntries(): void
    {
        $connection = $this->get('database_connection');

        $handler = new DbTableLogHandler($connection);

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

    public function testItDoesntRotateIfNoNeedTo(): void
    {
        $connection = $this->get('database_connection');

        $handler = new DbTableLogHandler($connection, 0);

        $handler->handle(
            [
                'message' => 'My old nice message',
                'context' => [],
                'level' =>  Logger::DEBUG,
                'level_name' => Logger::getLevelName(Logger::DEBUG),
                'channel' => 'authentication',
                'datetime' => new \DateTime('2012-01-01 13:43:25 GMT+2'),
                'extra' => array()
            ]
        );

        $handler->handle(
            [
                'message' => 'My new nice message',
                'context' => [],
                'level' =>  Logger::DEBUG,
                'level_name' => Logger::getLevelName(Logger::DEBUG),
                'channel' => 'authentication',
                'datetime' => new \DateTime('2013-01-01 13:43:25 GMT+2'),
                'extra' => array()
            ]
        );

        $handler->close();

        $this->assertLogTableIsSame(
            [
                [
                    'channel' => 'authentication',
                    'level' =>  Logger::DEBUG,
                    'message' => "[2012-01-01 13:43:25] authentication.DEBUG: My old nice message [] []\n",
                    'time' => '2012-01-01 11:43:25'
                ],
                [
                    'channel' => 'authentication',
                    'level' =>  Logger::DEBUG,
                    'message' => "[2013-01-01 13:43:25] authentication.DEBUG: My new nice message [] []\n",
                    'time' => '2013-01-01 11:43:25'
                ]
            ]
        );
    }

    public function testItRotatesOldEntries(): void
    {
        $connection = $this->get('database_connection');

        $handler = new DbTableLogHandler($connection, 10);

        $handler->handle(
            [
                'message' => 'My very old nice message',
                'context' => [],
                'level' =>  Logger::DEBUG,
                'level_name' => Logger::getLevelName(Logger::DEBUG),
                'channel' => 'authentication',
                'datetime' => (new \DateTime('11 days ago GMT+2'))->setTime(14,0),
                'extra' => array()
            ]
        );

        $handler->handle(
            [
                'message' => 'My old nice message',
                'context' => [],
                'level' =>  Logger::DEBUG,
                'level_name' => Logger::getLevelName(Logger::DEBUG),
                'channel' => 'authentication',
                'datetime' => (new \DateTime('11 days ago GMT+2'))->setTime(16,0),
                'extra' => array()
            ]
        );

        $lastEntryTime = (new \DateTime('today GMT+2'))->setTime(15,0);

        $handler->handle(
            [
                'message' => 'My new nice message',
                'context' => [],
                'level' =>  Logger::DEBUG,
                'level_name' => Logger::getLevelName(Logger::DEBUG),
                'channel' => 'authentication',
                'datetime' => $lastEntryTime,
                'extra' => array()
            ]
        );

        $handler->close();

        $this->assertLogTableIsSame(
            [
                [
                    'channel' => 'authentication',
                    'level' =>  Logger::DEBUG,
                    'message' => sprintf(
                        "[%s] authentication.DEBUG: My new nice message [] []\n",
                        $lastEntryTime->setTimezone(new \DateTimeZone('GMT+2'))->format('Y-m-d H:i:s')
                    ),
                    'time' => $lastEntryTime->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s')
                ]
            ]
        );
    }

    public function testItIntegratesWithMonolog()
    {
        $connection = $this->get('database_connection');

        $logger = new Logger('my_test_logger');

        $handler = new DbTableLogHandler($connection, 0);

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
        $connection->executeQuery(sprintf('DROP TABLE IF EXISTS %s', DbTableLogHandler::TABLE_NAME));
    }

    protected function getConfiguration()
    {
        return null;
    }

}

