<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Authentication\Sso\Log;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Monolog\Logger;

final class RotateLogCommandIntegration extends TestCase
{
    public function testItRotatesOldEntries(): void
    {
        $connection = $this->getConnection();
        $command = $this->get('Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\RotateLogCommand');

        $connection->insert(
            'pimee_sso_log',
            [
                'time' => $connection->convertToDatabaseValue(
                    (new \DateTime('11 days ago GMT+2'))->setTime(14, 0),
                    'datetime'
                ),
                'channel' => 'authentication',
                'level' => Logger::DEBUG,
                'message' => 'My very old nice message'
            ]
        );

        $connection->insert(
            'pimee_sso_log',
            [
                'time' => $connection->convertToDatabaseValue(
                    (new \DateTime('11 days ago GMT+2'))->setTime(16, 0),
                    'datetime'
                ),
                'channel' => 'authentication',
                'level' => Logger::DEBUG,
                'message' => 'My old nice message'
            ]
        );

        $lastEntryTime = (new \DateTime('today GMT+2'))->setTime(15, 0);

        $connection->insert(
            'pimee_sso_log',
            [
                'time' => $connection->convertToDatabaseValue($lastEntryTime, 'datetime'),
                'channel' => 'authentication',
                'level' => Logger::DEBUG,
                'message' => 'My new nice message'
            ]
        );

        $arguments = [
            'max-days' => 10
        ];

        $input = new ArrayInput($arguments);

        $output = new BufferedOutput();

        $command->run($input, $output);


        $this->assertLogTableIsSame(
            [
                [
                    'channel' => 'authentication',
                    'level' =>  Logger::DEBUG,
                    'message' => "My new nice message",
                    'time' => $lastEntryTime->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s')
                ]
            ]
        );
    }

    private function assertLogTableIsSame(array $expected): void
    {
        $connection = $this->getConnection();

        $actual = $connection->fetchAllAssociative(
            'SELECT channel, level, message, time FROM pimee_sso_log ORDER BY time ASC',
        );

        $this->assertEquals($expected, $actual);
    }

    protected function tearDown(): void
    {
        $connection = $this->getConnection();
        if ($connection->createSchemaManager()->tablesExist('pimee_sso_log') == true) {
            $connection->executeQuery('TRUNCATE TABLE pimee_sso_log');
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
