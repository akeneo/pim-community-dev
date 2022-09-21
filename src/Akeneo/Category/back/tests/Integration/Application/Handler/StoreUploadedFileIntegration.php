<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\back\tests\Integration\Application\Handler;

use Akeneo\Category\Application\Handler\StoreUploadedFile;
use Akeneo\Category\Domain\Filesystem\Storage;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StoreUploadedFileIntegration extends TestCase
{
    /** @test */
    public function it_stores_file(): void
    {
        $fileInfo = new \SplFileInfo($this->getFixturePath('akeneo.jpg'));

        $fileToUpload = new UploadedFile($fileInfo->getPathname(), $fileInfo->getFilename(), 'image/jpg');

        $file = $this->get(StoreUploadedFile::class)->__invoke($fileToUpload);

        $fileStorage = $this->get('akeneo_file_storage.file_storage.filesystem_provider')->getFilesystem(Storage::CATEGORY_STORAGE_ALIAS);
        $this->assertTrue($fileStorage->fileExists($file->getKey()));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
