<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\FindOneCatalogByIdQuery;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\FindOneCatalogByIdQuery
 */
class FindOneCatalogByIdQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItFindsTheCatalog(): void
    {
        $this->createUser('test');
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->createCatalog($id, 'Store US', 'test', isEnabled: false);

        $result = self::getContainer()->get(FindOneCatalogByIdQuery::class)->execute($id);

        $expected = new Catalog($id, 'Store US', 'test', false);

        $this->assertEquals($expected, $result);
    }

    public function testItReturnsNullIfUnknownId(): void
    {
        $result = self::getContainer()->get(FindOneCatalogByIdQuery::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $this->assertNull($result);
    }
}
