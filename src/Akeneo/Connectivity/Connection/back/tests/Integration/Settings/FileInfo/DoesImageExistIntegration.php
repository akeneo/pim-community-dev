<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Settings\FileInfo;

use Akeneo\Connectivity\Connection\Application\Settings\Service\DoesImageExistQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Settings\FileInfo\DoesImageExistQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class DoesImageExistIntegration extends TestCase
{
    private DoesImageExistQueryInterface $imageExistQuery;

    public function test_that_an_image_exist(): void
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

        $this->imageExistQuery = $this->get(DoesImageExistQuery::class);
    }

    private function addFileInfo(): void
    {
        $query = <<<SQL
    INSERT INTO akeneo_file_storage_file_info (file_key, original_filename, mime_type, size, extension)
    VALUES ('a/b/c/image.jpg', 'image.jpg', 'image/jpg', 42, 'jpg')
SQL;
        $this->get('database_connection')->executeQuery($query);
    }
}
