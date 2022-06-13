<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetUuidMappingFromProductIdentifiersQueryInterface;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUuidMappingFromProductIdentifiersQueryIntegration extends TestCase
{
    private GetUuidMappingFromProductIdentifiersQueryInterface $getUuidMappingFromProductIdentifiersQuery;

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
        $productIdentifier = 'product_1';
        $this->createProduct($productIdentifier);

        $idMapping = $this->getUuidMappingFromProductIdentifiersQuery->execute([$productIdentifier]);

        $expectedUuid = $this->getProductUuid($productIdentifier);
        Assert::assertTrue($idMapping->hasUuid($productIdentifier));
        $actualUuid = $idMapping->getUuid($productIdentifier);

        self::assertEquals($expectedUuid->toString(), $actualUuid->toString());
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

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
