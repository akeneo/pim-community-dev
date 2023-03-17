<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\FileSystem\Remover;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Filesystem\Storage;
use Akeneo\Category\Infrastructure\FileSystem\Remover\DeleteFilesFromPaths;
use Akeneo\Test\Integration\Configuration;

class DeleteCategoryFilesFromPathsIntegration extends CategoryTestCase
{
    /** @test */
    public function itDeletesCategoryFilesFromPaths(): void
    {
        $filesystem = $this->get('akeneo_file_storage.file_storage.filesystem_provider')->getFilesystem(Storage::CATEGORY_STORAGE_ALIAS);
        $filesystem->write('a_category/file1.jpg', 'foo');
        $this->insertFileStorage('a_category/file1.jpg', 'file1.jpg');
        $filesystem->write('a_category/file2.jpg', 'foo');
        $this->insertFileStorage('a_category/file2.jpg', 'file2.jpg');
        $filesystem->write('a_category/file3.jpg', 'foo');
        $this->insertFileStorage('a_category/file3.jpg', 'file3.jpg');
        $filesystem->write('a_category/file4.jpg', 'foo');
        $this->insertFileStorage('a_category/file4.jpg', 'file4.jpg');

        ($this->get(DeleteFilesFromPaths::class))(['a_category/file1.jpg', 'a_category/file2.jpg']);

        $categoryFiles = $filesystem->listContents('a_category')->toArray();
        $this->assertSame('a_category/file3.jpg', $categoryFiles[0]->path());
        $this->assertSame('a_category/file4.jpg', $categoryFiles[1]->path());

        $this->assertFalse($this->fileStorageExists('a_category/file1.jpg'));
        $this->assertFalse($this->fileStorageExists('a_category/file2.jpg'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
