<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\DBAL\Connection;

final class ConnectionInstaller implements FixtureInstaller
{
    use InstallCatalogTrait;

    private FileStorerInterface $fileStorer;

    private CreateConnectionHandler $createConnectionHandler;

    private UpdateConnectionHandler $updateConnectionHandler;

    private string $defaultUserRoleId;
    private string $defaultUserGroupId;

    private Connection $dbConnection;

    public function __construct(
        Connection $dbConnection,
        FileStorerInterface $fileStorer,
        CreateConnectionHandler $createConnectionHandler,
        UpdateConnectionHandler $updateConnectionHandler
    ) {
        $this->fileStorer = $fileStorer;
        $this->createConnectionHandler = $createConnectionHandler;
        $this->updateConnectionHandler = $updateConnectionHandler;
        $this->dbConnection = $dbConnection;
        $this->defaultUserRoleId = '';
        $this->defaultUserGroupId = '';
    }

    public function install(): void
    {
        $this->defaultUserRoleId = $this->retrieveDefaultUserRoleId();
        $this->defaultUserGroupId = $this->retrieveDefaultUserGroupId();

        $connections = $this->loadConnectionFixtures();
        foreach ($connections as $connection) {
            $this->installConnection($connection);
        }
    }

    private function installConnection(array $connectionData): void
    {
        $createCommand = new CreateConnectionCommand(
            $connectionData['code'],
            $connectionData['label'],
            $connectionData['flow_type'],
            $connectionData['auditable'],
        );

        $connection = $this->createConnectionHandler->handle($createCommand);

        $this->updateConnectionImage($connection, $connectionData['image']);
    }

    private function updateConnectionImage(ConnectionWithCredentials $connection, string $imageName): void
    {
        $image = $this->uploadImage($imageName);

        $updateCommand = new UpdateConnectionCommand(
            $connection->code(),
            $connection->label(),
            $connection->flowType(),
            $image->getKey(),
            $this->defaultUserRoleId,
            $this->defaultUserGroupId,
            $connection->auditable()
        );

        $this->updateConnectionHandler->handle($updateCommand);
    }

    private function uploadImage(string $file): FileInfoInterface
    {
        $rawFile = new \SplFileInfo($this->getConnectionImageFixturesPath(). '/' . $file);

        return $this->fileStorer->store($rawFile, FileStorage::CATALOG_STORAGE_ALIAS);
    }

    private function retrieveDefaultUserRoleId(): string
    {
        $query = <<<SQL
SELECT id FROM oro_access_role WHERE role = 'ROLE_USER';
SQL;
        return strval($this->dbConnection->executeQuery($query)->fetchColumn());
    }

    private function retrieveDefaultUserGroupId(): string
    {
        $query = <<<SQL
SELECT id FROM oro_access_group WHERE name = 'IT support';
SQL;
        return strval($this->dbConnection->executeQuery($query)->fetchColumn());
    }

    private function loadConnectionFixtures(): array
    {
        $connections = file_get_contents($this->getConnectionFixturesPath());

        return json_decode($connections, true, 512, JSON_THROW_ON_ERROR);
    }
}
