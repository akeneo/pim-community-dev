<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ImageFile\DeleteCategoryImageFile;
use Akeneo\Category\Domain\ImageFile\Storage;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteCategoryImageFileSqlIntegration extends CategoryTestCase
{
    public function testItDeletesCategoryImageFile(): void
    {
        $filesystem = $this->get('akeneo_file_storage.file_storage.filesystem_provider')->getFilesystem(Storage::CATEGORY_STORAGE_ALIAS);

        $fileName = 'file1.jpg';
        $filePath = 'a_category/' . $fileName;

        $filesystem->write($filePath, 'foo');
        $this->insertFileStorage($filePath, $fileName);

        $this->assertTrue($filesystem->fileExists($filePath));
        $this->assertTrue($this->fileStorageExists($filePath));

        ($this->get(DeleteCategoryImageFile::class))($filePath);

        $this->assertFalse($filesystem->fileExists($filePath));
        $this->assertFalse($this->fileStorageExists($filePath));
    }
}
