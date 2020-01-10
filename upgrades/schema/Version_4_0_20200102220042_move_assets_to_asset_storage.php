<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\IrreversibleMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Move assets create in 3.2 from the catalogStorage to the assetStorage
 */
final class Version_4_0_20200102220042_move_assets_to_asset_storage extends AbstractMigration implements ContainerAwareInterface
{
    /** * @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getDescription(): string
    {
        return 'Move assets create in 3.2 from the catalogStorage to the assetStorage.';
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function up(Schema $schema) : void
    {
        $this->connection->getWrappedConnection()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

        $query = <<<SQL
            SELECT file_info.file_key
              FROM
                akeneo_asset_manager_asset asset,
                JSON_TABLE(
                    asset.value_collection,
                    '$.*.data.filePath'
                    COLUMNS(
                        file_path VARCHAR(255) PATH '$')
                    ) asset_file
                JOIN akeneo_file_storage_file_info file_info ON (file_info.file_key = asset_file.file_path COLLATE utf8mb4_general_ci)
                WHERE file_info.storage = 'catalogStorage'
SQL;

        $stmt = $this->connection->query($query);

        while ($row = $stmt->fetch()) {
            $this->copyFileFromCatalogToAssetStorage($row['file_key']);
        }

        $query = <<<SQL
            UPDATE akeneo_file_storage_file_info f
              SET f.storage = 'assetStorage'
            WHERE f.storage = 'catalogStorage' AND f.file_key IN (
                SELECT file_key FROM (
                    SELECT file_info.file_key
                      FROM
                        akeneo_asset_manager_asset asset,
                        JSON_TABLE(
                            asset.value_collection,
                            '$.*.data.filePath'
                            COLUMNS(
                                file_path VARCHAR(255) PATH '$')
                            ) asset_file
                        JOIN akeneo_file_storage_file_info file_info ON (file_info.file_key = asset_file.file_path COLLATE utf8mb4_general_ci)
                        WHERE file_info.storage = 'catalogStorage'
                    ) asset_file
                )

SQL;
        $this->connection->query($query);
    }

    private function copyFileFromCatalogToAssetStorage(string $filePath)
    {
        $mountManager = $this->container->get('oneup_flysystem.mount_manager');

        $catalogStorage = $mountManager->getFilesystem('catalogStorage');
        $assetStorage = $mountManager->getFilesystem('assetStorage');

        if ($catalogStorage->has($filePath)) {
            $stream = $catalogStorage->readStream($filePath);

            $assetStorage->putStream($filePath, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        } else {
            $this->logger->warning(
                sprintf('Unable to find file path %s in catalogStorage', $filePath)
            );
        }
    }
}
