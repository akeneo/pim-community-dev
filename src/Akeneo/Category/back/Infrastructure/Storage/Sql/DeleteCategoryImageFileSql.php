<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\ImageFile\DeleteCategoryImageFile;
use Akeneo\Category\Domain\ImageFile\Storage;
use Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteCategoryImageFileSql implements DeleteCategoryImageFile
{
    public function __construct(
        private readonly Connection $connection,
        private readonly FilesystemProvider $filesystemProvider,
        private readonly PreviewGeneratorInterface $previewGenerator,
    ) {
    }

    public function __invoke(string $filePath): void
    {
        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::CATEGORY_STORAGE_ALIAS);

        foreach (PreviewGeneratorRegistry::IMAGE_TYPES as $type) {
            $this->previewGenerator->remove(
                data: base64_encode($filePath),
                type: $type,
            );
        }
        $fileSystem->delete($filePath);
        $this->deleteFileInfo($filePath);
    }

    private function deleteFileInfo(string $filePath): void
    {
        $this->connection->executeStatement(
            <<<SQL
                DELETE FROM `akeneo_file_storage_file_info`
                WHERE file_key = :file_key
            SQL,
            [
                'file_key' => $filePath,
            ],
            [
                'file_key' => \PDO::PARAM_STR,
            ],
        );
    }
}
