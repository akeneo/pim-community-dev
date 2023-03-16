<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\FileSystem\Remover;

use Akeneo\Category\Domain\Filesystem\Storage;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToDeleteFile;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteCategoryFilesFromPaths implements DeleteFilesFromPaths
{
    public function __construct(
        private readonly FileSystemProvider $fileSystemProvider,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string> $filePaths
     */
    public function __invoke(array $filePaths): void
    {
        $fileSystem = $this->fileSystemProvider->getFilesystem(Storage::CATEGORY_STORAGE_ALIAS);

        foreach ($filePaths as $filePath) {
            try {
                $fileSystem->delete($filePath);
            } catch (UnableToDeleteFile|FilesystemException $e) {
                $this->logger->error('Category file could not be deleted.', [
                    'data' => [
                        'path' => $filePath,
                        'error' => $e->getMessage(),
                    ],
                ]);
            }
        }
    }
}
