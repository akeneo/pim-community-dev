<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product\IsValidCatalogProductQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsValidCatalogProductQueryTest extends IntegrationTestCase
{
    private ?IsValidCatalogProductQuery $isValidCatalogProductQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->isValidCatalogProductQuery = self::getContainer()->get(IsValidCatalogProductQuery::class);
    }

    public function testItDoesNotValidatesAProductNotBelongingToTheCatalog(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog($catalogId, 'Store US', 'owner');
        $this->enableCatalog($catalogId);
        $this->setCatalogProductSelection($catalogId, [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);

        $productDisabled = $this->createProduct('tshirt-green', [new SetEnabled(false)]);

        $result = $this->isValidCatalogProductQuery->execute($catalogId, (string) $productDisabled->getUuid());

        $this->assertFalse($result, 'Product should not belong to the catalog');
    }

    public function testItValidatesAProductBelongingToTheCatalog(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog($catalogId, 'Store US', 'owner');
        $this->enableCatalog($catalogId);
        $this->setCatalogProductSelection($catalogId, [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);

        $productEnabled = $this->createProduct('tshirt-blue', [new SetEnabled(true)]);

        $result = $this->isValidCatalogProductQuery->execute($catalogId, (string) $productEnabled->getUuid());

        $this->assertTrue($result, 'Product should belong to the catalog');
    }

    public function testItDoesNotValidatesAnUnknownProduct(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog($catalogId, 'Store US', 'owner');
        $this->enableCatalog($catalogId);
        $this->setCatalogProductSelection($catalogId, [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);

        $this->createProduct('tshirt-green', [new SetEnabled(false)]);

        $result = $this->isValidCatalogProductQuery->execute($catalogId, 'c335c87e-ec23-4c5b-abfa-0638f141933a');

        $this->assertFalse($result, 'Product should not belong to the catalog');
    }
}
