<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogProductSelectionCriteriaQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogProductSelectionCriteriaQuery
 */
class GetCatalogProductSelectionCriteriaQueryTest extends IntegrationTestCase
{
    private ?GetCatalogProductSelectionCriteriaQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetCatalogProductSelectionCriteriaQuery::class);
    }

    public function testItGetsProductSelectionCriteria(): void
    {
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createUser('owner');
        $this->createCatalog($id, 'Store US', 'owner');

        $result = $this->query->execute($id);

        $expectedCriteria = [
            [
                'field' => 'enabled',
                'operator' => '=',
                'value' => true,
            ],
        ];

        Assert::assertEquals($expectedCriteria, $result);
    }

    public function testItThrowsOnInvalidCatalogId(): void
    {
        $this->expectException(\LogicException::class);

        $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');
    }
}
