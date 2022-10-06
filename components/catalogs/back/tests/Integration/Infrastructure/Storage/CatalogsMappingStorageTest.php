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
    private ?CatalogsMappingStorage $storage;
    private ?Filesystem $filesystem;

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

        $this->storage = self::getContainer()->get(CatalogsMappingStorage::class);
        $this->filesystem = self::getContainer()->get('oneup_flysystem.catalogs_mapping_filesystem');
    }

    public function testItCreatesAFile(): void
    {
        $this->storage->write('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json', self::SCHEMA);

        $contents = $this->filesystem->read('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json');

        $this->assertSame(self::SCHEMA, $contents);
    }

    public function testItChecksIfAFileExists(): void
    {
        $this->filesystem->write('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json', self::SCHEMA);

        $exists = $this->storage->exists('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json');

        $this->assertTrue($exists);
    }

    public function testItReturnsAFileContents(): void
    {
        $this->filesystem->write('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json', self::SCHEMA);

        $contents = \stream_get_contents($this->storage->read('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json'));

        $this->assertSame(self::SCHEMA, $contents);
    }

    public function testItDeletesAFile(): void
    {
        $this->filesystem->write('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json', self::SCHEMA);

        $this->storage->delete('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json');

        $this->assertFalse($this->filesystem->fileExists('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json'));
    }
}
