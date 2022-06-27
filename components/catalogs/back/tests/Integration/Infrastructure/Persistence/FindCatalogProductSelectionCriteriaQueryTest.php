<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\FindCatalogProductSelectionCriteriaQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\FindCatalogProductSelectionCriteriaQuery
 */
class FindCatalogProductSelectionCriteriaQueryTest extends IntegrationTestCase
{
    private ?FindCatalogProductSelectionCriteriaQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(FindCatalogProductSelectionCriteriaQuery::class);
    }

    public function testItGetsNullResultOnInvalidCatalogId(): void
    {
        $result = $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        Assert::assertNull($result);
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
}
