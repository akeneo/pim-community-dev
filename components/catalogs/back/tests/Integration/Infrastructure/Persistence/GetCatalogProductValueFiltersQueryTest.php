<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\GetCatalogProductValueFiltersQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\GetCatalogProductValueFiltersQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\GetCatalogProductValueFiltersQuery
 */
class GetCatalogProductValueFiltersQueryTest extends IntegrationTestCase
{
    private ?GetCatalogProductValueFiltersQueryInterface $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetCatalogProductValueFiltersQuery::class);
    }

    public function testItGetsProductValueFilters(): void
    {
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createUser('owner');
        $this->createCatalog($id, 'Store US', 'owner');
        $this->setCatalogProductValueFilters(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            ['channel' => ['ecommerce', 'print']]
        );

        $result = $this->query->execute($id);

        $expectedFilters = [
            'channel' => ['ecommerce', 'print']
        ];

        Assert::assertEquals($expectedFilters, $result);
    }

    public function testItThrowsOnInvalidCatalogId(): void
    {
        $this->expectException(\LogicException::class);

        $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');
    }
}
