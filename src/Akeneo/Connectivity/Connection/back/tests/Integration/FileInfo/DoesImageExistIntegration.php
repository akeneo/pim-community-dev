<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\FileInfo;

use Akeneo\Connectivity\Connection\Application\Settings\Service\DoesImageExistQueryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class DoesImageExistIntegration extends TestCase
{
    /** @var DoesImageExistQueryInterface */
    private $imageExistQuery;

    public function test_that_an_image_exist()
    {
        $this->addFileInfo();
        $exist = $this->imageExistQuery->execute('a/b/c/image.jpg');

        Assert::assertTrue($exist);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->imageExistQuery = $this->get('akeneo_connectivity.connection.service.file_info.does_image_exist');
    }

    private function addFileInfo()
    {
        $query = <<<SQL
    INSERT INTO akeneo_file_storage_file_info (file_key, original_filename, mime_type, size, extension)
    VALUES ('a/b/c/image.jpg', 'image.jpg', 'image/jpg', 42, 'jpg')
SQL;
        $this->get('database_connection')->executeQuery($query);
    }
}
