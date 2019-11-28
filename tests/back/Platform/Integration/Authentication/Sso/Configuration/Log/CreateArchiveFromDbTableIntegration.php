<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Authentication\Sso\Log;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\CreateArchiveFromDbTable;
use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\DbTableLogHandler;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\Test\Integration\TestCase;
use Monolog\Logger;

final class CreateArchiveFromDbTableIntegration extends TestCase
{
    /** @var \SplFileInfo */
    private $archiveFile;

    public function testItCreatesArchiveFromInsertedLogEntries(): void
    {
        $connection = $this->get('database_connection');
        $configRepo = new EnabledConfigurationRepository();

        $handler = new DbTableLogHandler($configRepo, $connection, 1);

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
        $configRepo = new EnabledConfigurationRepository();
        $connection = $this->get('database_connection');
        $handler = new DbTableLogHandler($configRepo, $connection);

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
        if ($connection->getSchemaManager()->tablesExist('pimee_sso_log') == true) {
            $connection->executeQuery('TRUNCATE TABLE pimee_sso_log');
        }

    }

    protected function getConfiguration()
    {
        return null;
    }
}

final class EnabledConfigurationRepository implements Repository
{
    public function find(string $code): Configuration
    {
        return Configuration::fromArray(
            'enabledConfiguration',
            [
                'isEnabled' => true,
                'identityProvider' => [
                    'entityId' => 'http://www.example.com/',
                    'signOnUrl' => 'http://www.example.com/signon',
                    'logoutUrl' => 'http://www.example.com/logout',
                    'certificate' => 'my mock certificate'
                ],
                'serviceProvider' => [
                    'entityId' => 'http://www.example.com/',
                    'certificate' => 'my mock certificate',
                    'privateKey' => 'my mock private key'
                ]
            ]
        );
    }

    public function save(Configuration $configurationRoot): void
    {
        throw new \LogicException("Mock configuration repository will not save configuration.");
    }
}
