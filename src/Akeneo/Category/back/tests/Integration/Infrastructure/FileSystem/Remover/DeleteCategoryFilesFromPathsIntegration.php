<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\FileSystem\Remover;

use Akeneo\Category\Domain\Filesystem\Storage;
use Akeneo\Category\Infrastructure\FileSystem\Remover\DeleteFilesFromPaths;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class DeleteCategoryFilesFromPathsIntegration extends TestCase
{
    /** @test */
    public function itDeletesCategoryFilesFromPaths(): void
    {
        $filesystem = $this->get('akeneo_file_storage.file_storage.filesystem_provider')->getFilesystem(Storage::CATEGORY_STORAGE_ALIAS);
        $filesystem->write('a_category/file1.jpg', 'foo');
        $filesystem->write('a_category/file2.jpg', 'foo');
        $filesystem->write('a_category/file3.jpg', 'foo');
        $filesystem->write('a_category/file4.jpg', 'foo');

        ($this->get(DeleteFilesFromPaths::class))(['a_category/file1.jpg', 'a_category/file2.jpg']);

        $categoryFiles = $filesystem->listContents('a_category')->toArray();
        $this->assertSame('a_category/file3.jpg', $categoryFiles[0]->path());
        $this->assertSame('a_category/file4.jpg', $categoryFiles[1]->path());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
