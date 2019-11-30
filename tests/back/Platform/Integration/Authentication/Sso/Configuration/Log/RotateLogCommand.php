<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Authentication\Sso\Log;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\RotateLogCommand;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Monolog\Logger;

final class RotateLogCommandIntegration extends TestCase
{
    public function testItRotatesOldEntries(): void
    {
        $connection = $this->get('database_connection');
        $command = $this->get('Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\RotateLogCommand');

        $connection->insert(
            'pimee_sso_log',
            [
                'time' => $connection->convertToDatabaseValue(
                    (new \DateTime('11 days ago GMT+2'))->setTime(14,0),
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
                    (new \DateTime('11 days ago GMT+2'))->setTime(16,0),
                    'datetime'
                ),
                'channel' => 'authentication',
                'level' => Logger::DEBUG,
                'message' => 'My old nice message'
            ]
        );

        $lastEntryTime = (new \DateTime('today GMT+2'))->setTime(15,0);

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
            'max-days'=> 10
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
        $connection =  $this->get('database_connection');

        $actual = $connection->fetchAll(
            'SELECT channel, level, message, time FROM pimee_sso_log ORDER BY time ASC',
        );

        $this->assertEquals($expected, $actual);
    }

    protected function tearDown(): void
    {
        $connection = $this->get('database_connection');
        if ($connection->getSchemaManager()->tablesExist('pimee_sso_log') == true) {
            $connection->executeQuery('TRUNCATE TABLE pimee_sso_log');
        }
    }

    protected function getConfiguration()
    {
        return null;
    }
}
