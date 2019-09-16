<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Authentication\Sso\Log;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\DbTableLogHandler;
use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\CreateArchiveFromDbTable;
use Akeneo\Test\Integration\TestCase;
use Monolog\Logger;

final class CreateArchiveFromDbTableIntegration extends TestCase
{

    /** @var \SplFileInfo */
    private $archiveFile;

    public function testItCreatesArchiveFromInsertedLogEntries(): void
    {
        $connection = $this->get('database_connection');
        $handler = new DbTableLogHandler($connection, 1);

        $archiveCreate = $this->get('akeneo_authentication.sso.log.create_archive_from_db_table');

        $handler->handle(
            [
                'message' => 'An error message',
                'context' => [],
                'level' =>  Logger::ERROR,
                'level_name' => Logger::getLevelName(Logger::ERROR),
                'channel' => 'authentication',
                'datetime' => new \DateTime('2013-01-01 13:43:25 GMT+2'),
                'extra' => array()
            ]
        );

        $handler->handle(
            [
                'message' => 'Another latter error message',
                'context' => [],
                'level' =>  Logger::ERROR,
                'level_name' => Logger::getLevelName(Logger::ERROR),
                'channel' => 'authentication',
                'datetime' => new \DateTime('2013-01-01 13:43:26 GMT+2'),
                'extra' => array()
            ]
        );


        $this->archiveFile = $archiveCreate->create();

        $this->assertArchiveContentIsSame(
            "[2013-01-01 13:43:25] authentication.ERROR: An error message [] []\n".
            "[2013-01-01 13:43:26] authentication.ERROR: Another latter error message [] []\n"
        );
    }

    public function testItCreatesAnEmptyArchiveFromNonExistentTable(): void
    {
        $archiveCreate = $this->get('akeneo_authentication.sso.log.create_archive_from_db_table');
        $this->archiveFile = $archiveCreate->create();

        $this->assertArchiveContentIsSame("");
    }

    public function testItCreatesAnEmptyArchiveFromEmptyDbTable(): void
    {
        $connection = $this->get('database_connection');
        $handler = new DbTableLogHandler($connection, 1);

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

        $handler->close();

        $archiveCreate = $this->get('akeneo_authentication.sso.log.create_archive_from_db_table');
        $this->archiveFile = $archiveCreate->create();

        $this->assertArchiveContentIsSame("");
    }

    private function assertArchiveContentIsSame(string $expected)
    {
        $zipArchive = new \ZipArchive();
        $zipArchive->open($this->archiveFile->getPathname());
        $actual = $zipArchive->getFromName(CreateArchiveFromDbTable::LOG_FILENAME);
        $zipArchive->close();

        $this->assertEquals($expected, $actual);
    }

    public function tearDown(): void
    {
        if (null !== $this->archiveFile) {
            unlink($this->archiveFile->getPathname());
        }
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

