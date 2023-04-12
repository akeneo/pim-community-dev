<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Storage;

use Akeneo\Catalogs\Infrastructure\Storage\CatalogsMappingStorage;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use League\Flysystem\Filesystem;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogsMappingStorageTest extends IntegrationTestCase
{
    private const SCHEMA = <<<'EOS'
{
  "$schema": "https://api.akeneo.com/schema/2022-08/product",
  "properties": {
    "name": {
      "title": "Name",
      "type": "string"
    },
    "body_html": {
      "title": "Description",
      "type": "string"
    }
  }
}
EOS;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeData();
    }

    public function testItCreatesAFile(): void
    {
        self::getContainer()->get(CatalogsMappingStorage::class)->write('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json', self::SCHEMA);

        $contents = self::getContainer()->get('oneup_flysystem.catalogs_mapping_filesystem')->read('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json');

        $this->assertSame(self::SCHEMA, $contents);
    }

    public function testItChecksIfAFileExists(): void
    {
        self::getContainer()->get('oneup_flysystem.catalogs_mapping_filesystem')->write('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json', self::SCHEMA);

        $exists = self::getContainer()->get(CatalogsMappingStorage::class)->exists('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json');

        $this->assertTrue($exists);
    }

    public function testItReturnsAFileContents(): void
    {
        self::getContainer()->get('oneup_flysystem.catalogs_mapping_filesystem')->write('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json', self::SCHEMA);

        $contents = \stream_get_contents(self::getContainer()->get(CatalogsMappingStorage::class)->read('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json'));

        $this->assertSame(self::SCHEMA, $contents);
    }

    public function testItDeletesAFile(): void
    {
        self::getContainer()->get('oneup_flysystem.catalogs_mapping_filesystem')->write('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json', self::SCHEMA);

        self::getContainer()->get(CatalogsMappingStorage::class)->delete('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json');

        $this->assertFalse(self::getContainer()->get('oneup_flysystem.catalogs_mapping_filesystem')->fileExists('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json'));
    }
}
