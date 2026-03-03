<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Unit\Application\Command\PurgeOrphanCategoryImageFiles;

use Akeneo\Category\Application\Command\PurgeOrphanCategoryImageFiles\PurgeOrphanCategoryImageFilesCommand;
use Akeneo\Category\Application\Command\PurgeOrphanCategoryImageFiles\PurgeOrphanCategoryImageFilesCommandHandler;
use Akeneo\Category\Domain\DTO\IteratorStatus;
use Akeneo\Category\Domain\ImageFile\DeleteCategoryImageFile;
use Akeneo\Category\Domain\ImageFile\GetOrphanCategoryImageFilePaths;
use Akeneo\Category\Domain\ImageFile\Storage;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeOrphanCategoryImageFilesCommandHandlerTest extends TestCase
{
    public function testItPurgesOrphanCategoryImageFiles(): void
    {
        $fileSystem = $this->createMock(FilesystemOperator::class);
        $fileSystemProvider = $this->createMock(FilesystemProvider::class);
        $fileSystemProvider
            ->method('getFilesystem')
            ->with(Storage::CATEGORY_STORAGE_ALIAS)
            ->willReturn($fileSystem);

        $getOrphanCategoryImageFilePaths = $this->createMock(GetOrphanCategoryImageFilePaths::class);
        $getOrphanCategoryImageFilePaths
            ->method('__invoke')
            ->willReturn(new \ArrayIterator([
                IteratorStatus::inProgress(),
                IteratorStatus::inProgress(),
                IteratorStatus::done(['a_category/file1.jpg', 'a_category/file2.jpg']),
            ]));

        $deleteCategoryImageFile = $this->createMock(DeleteCategoryImageFile::class);
        $deleteCategoryImageFile
            ->method('__invoke')
            ->withConsecutive(
                ['a_category/file1.jpg'],
                ['a_category/file2.jpg'],
            );

        $handler = new PurgeOrphanCategoryImageFilesCommandHandler(
            $fileSystemProvider,
            $getOrphanCategoryImageFilePaths,
            $deleteCategoryImageFile,
        );

        $purgeOrphanCategoryImageFilesCommand = $this->createMock(PurgeOrphanCategoryImageFilesCommand::class);
        $results = iterator_to_array($handler($purgeOrphanCategoryImageFilesCommand));

        $this->assertCount(5, $results);
        $this->assertEquals([
            IteratorStatus::inProgress(),
            IteratorStatus::inProgress(),
            IteratorStatus::inProgress(),
            IteratorStatus::inProgress(),
            IteratorStatus::done(),
        ], $results);
    }
}
