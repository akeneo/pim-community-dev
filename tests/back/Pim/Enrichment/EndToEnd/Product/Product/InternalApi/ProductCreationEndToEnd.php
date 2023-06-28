<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\InternalApiTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductCreationEndToEnd extends InternalApiTestCase
{
    private GetConnectorProducts $getConnectorProductsQuery;

    public function setUp(): void
    {
        parent::setUp();

        $this->getConnectorProductsQuery = $this->get('akeneo.pim.enrichment.product.connector.get_product_from_uuids');
        $this->authenticate($this->getAdminUser());
    }

    public function testThatWeCanFetchANewlyCreatedProductFromTheUI(): void
    {
        $identifier = 'new_product';
        $this->createProductWithInternalApi($identifier);

        $admin = $this->getAdminUser();

        $createdProduct = $this->getConnectorProductsQuery
            ->fromProductUuids($this->getProductUuidsFromProductIdentifiers([$identifier]), $admin->getId(), null, null, null)
            ->connectorProducts();

        $this->assertCount(1, $createdProduct);
        $this->assertContainsOnlyInstancesOf(ConnectorProduct::class, $createdProduct);
        $this->assertEquals($identifier, $createdProduct[0]->identifier());
    }

    public function testThatWeCanCreateAProductWithoutIdentifier(): void
    {
        $previousCount = $this->getProductsCount();
        $this->createProductWithInternalApi('');

        $this->assertEquals($previousCount + 1, $this->getProductsCount());
    }

    private function createProductWithInternalApi(string $identifier): void
    {
        $this->client->request(
            'POST',
            '/enrich/product/rest',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode(['identifier' => $identifier])
        );
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function getAdminUser(): UserInterface
    {
        return self::getContainer()->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }

    /**
     * @param array<string> $productIdentifiers
     * @return array<UuidInterface>
     */
    private function getProductUuidsFromProductIdentifiers(array $productIdentifiers): array
    {
        $sql = <<<SQL
WITH main_identifier AS (
    SELECT id
    FROM pim_catalog_attribute
    WHERE main_identifier = 1
    LIMIT 1
)
SELECT BIN_TO_UUID(product_uuid) AS uuid
FROM pim_catalog_product_unique_data
WHERE raw_data IN (:identifiers)
    AND attribute_id = (SELECT id FROM main_identifier) 
SQL;

        return array_map(
            fn (string $uuidStr): UuidInterface => Uuid::fromString($uuidStr),
            self::getContainer()->get('database_connection')->fetchFirstColumn(
                $sql,
                ['identifiers' => $productIdentifiers],
                ['identifiers' => Connection::PARAM_STR_ARRAY]
            )
        );
    }

    private function getProductsCount(): int
    {
        $sql = <<<SQL
SELECT COUNT(1) FROM pim_catalog_product
SQL;

        return \intval(self::getContainer()->get('database_connection')->executeQuery($sql)->fetchOne());
    }
}
