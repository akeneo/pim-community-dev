<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\DeleteFileStorage;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Test\Integration\Configuration;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteFileStorageSqlIntegration extends CategoryTestCase
{
    public function testItDeletesFileStorage(): void
    {
        $this->insertFileStorage('a_category/file1.jpg', 'file1.jpg');
        $this->insertFileStorage('a_category/file2.jpg', 'file2.jpg');

        ($this->get(DeleteFileStorage::class))('a_category/file2.jpg');

        $this->assertTrue($this->fileStorageExists('a_category/file1.jpg'));
        $this->assertFalse($this->fileStorageExists('a_category/file2.jpg'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
