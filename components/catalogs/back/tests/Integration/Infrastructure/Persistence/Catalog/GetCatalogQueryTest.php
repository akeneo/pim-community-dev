<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Domain\Catalog\Catalog;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogQuery
 */
class GetCatalogQueryTest extends IntegrationTestCase
{
    private ?GetCatalogQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetCatalogQuery::class);
    }

    public function testItGetsCatalog(): void
    {
        $this->createUser('test');
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->createCatalog($id, 'Store US', 'test');

        $this->setCatalogProductValueFilters(
            $id,
            ['channels' => ['ecommerce', 'print']]
        );

        $result = $this->query->execute($id);

        $expected = new Catalog(
            $id,
            'Store US',
            'test',
            false,
            [
                [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
            ],
            ['channels' => ['ecommerce', 'print']]
        );

        $this->assertEquals($expected, $result);
    }
}
