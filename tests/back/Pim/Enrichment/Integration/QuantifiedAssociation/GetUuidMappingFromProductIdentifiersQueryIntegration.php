<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetUuidMappingFromProductIdentifiersQueryInterface;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUuidMappingFromProductIdentifiersQueryIntegration extends TestCase
{
    /** @var GetUuidMappingFromProductIdentifiersQueryInterface */
    private $getUuidMappingFromProductIdentifiersQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getUuidMappingFromProductIdentifiersQuery = $this->get('akeneo.pim.enrichment.product.query.quantified_association.get_uuid_mapping_from_product_identifiers_query');
    }

    /**
     * @test
     */
    public function it_fetches_the_uuid_mapping_given_some_product_identifiers()
    {
        $wasColumnAdded = false;
        if (!$this->uuidColumnExists()) {
            $this->addUuidColumn();
            $wasColumnAdded = true;
        }

        $productIdentifier = 'product_1';
        $this->createProduct($productIdentifier);

        $idMapping = $this->getUuidMappingFromProductIdentifiersQuery->execute([$productIdentifier]);

        $actualUuid = $idMapping->getUuid($productIdentifier);

        $expectedUuid = $this->getProductUuid($productIdentifier);

        self::assertEquals($expectedUuid->toString(), $actualUuid->toString());

        if ($wasColumnAdded) {
            $this->dropUuidColumn();
        }
    }

    public function it_returns_empty_mapping_if_uuid_column_does_not_exist()
    {
        $wasColumnDropped = false;
        if ($this->uuidColumnExists()) {
            $this->dropUuidColumn();
            $wasColumnDropped = true;
        }

        $productIdentifier = 'product_1';
        $this->createProduct($productIdentifier);

        $idMapping = $this->getUuidMappingFromProductIdentifiersQuery->execute([$productIdentifier]);
        self::assertEquals([], $idMapping);

        if ($wasColumnDropped) {
            $this->addUuidColumn();
        }
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProduct(string $productIdentifier): void
    {
        $product = new Product();
        $this->get('pim_catalog.updater.product')->update(
            $product,
            [
                'family' => 'familyA',
                'values' => [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => $productIdentifier,
                        ],
                    ],
                ],
            ]
        );

        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function getProductUuid(string $productIdentifier): UuidInterface
    {
        $result = $this->getConnection()->fetchOne('SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier=:identifier', ['identifier' => $productIdentifier]);

        return Uuid::fromString($result);
    }

    private function addUuidColumn()
    {
        $this->getConnection()->executeQuery('ALTER TABLE pim_catalog_product ADD uuid BINARY(16) DEFAULT NULL AFTER id, LOCK=NONE, ALGORITHM=INPLACE');
    }

    private function dropUuidColumn()
    {
        $this->getConnection()->executeQuery('ALTER TABLE pim_catalog_product DROP COLUMN uuid, LOCK=NONE, ALGORITHM=INPLACE');
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function uuidColumnExists(): bool
    {
        $rows = $this->getConnection()->fetchAllAssociative('SHOW COLUMNS FROM pim_catalog_product LIKE "uuid"');

        return count($rows) >= 1;
    }
}
