<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\PurgeOrphanCategoryImageFiles;

use Akeneo\Category\Domain\DTO\IteratorStatus;
use Akeneo\Category\Domain\ImageFile\DeleteCategoryImageFile;
use Akeneo\Category\Domain\ImageFile\GetOrphanCategoryImageFilePaths;
use Akeneo\Category\Domain\ImageFile\Storage;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeOrphanCategoryImageFilesCommandHandler
{
    public function __construct(
        private readonly FilesystemProvider $filesystemProvider,
        private readonly GetOrphanCategoryImageFilePaths $getOrphanCategoryImageFilePaths,
        private readonly DeleteCategoryImageFile $deleteCategoryImageFile,
    ) {
    }

    /**
     * @return iterable<IteratorStatus>
     */
    public function __invoke(PurgeOrphanCategoryImageFilesCommand $command): iterable
    {
        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::CATEGORY_STORAGE_ALIAS);

        $iterator = ($this->getOrphanCategoryImageFilePaths)();

        $filePaths = [];
        foreach ($iterator as $status) {
            if ($status->isDone) {
                $filePaths = $status->value;
            } else {
                yield IteratorStatus::inProgress();
            }
        }
        if (empty($filePaths)) {
            yield IteratorStatus::done();
        }

        foreach ($filePaths as $filePath) {
            if ($fileSystem->fileExists($filePath) && $fileSystem->lastModified($filePath) > time() - 86400 /* 1 day */) {
                continue;
            }

            ($this->deleteCategoryImageFile)($filePath);

            yield IteratorStatus::inProgress();
        }

        yield IteratorStatus::done();
    }
}
