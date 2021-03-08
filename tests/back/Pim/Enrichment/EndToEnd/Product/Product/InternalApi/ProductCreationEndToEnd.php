<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\InternalApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductCreationEndToEnd extends InternalApiTestCase
{
    private ProductQueryBuilderFactoryInterface $pqbFactory;
    private GetConnectorProducts $getConnectorProductsQuery;

    public function setUp(): void
    {
        parent::setUp();

        $this->pqbFactory = $this->get('pim_catalog.query.product_query_builder_from_size_factory_external_api');
        $this->getConnectorProductsQuery = $this->get('akeneo.pim.enrichment.product.connector.get_product_from_identifiers');
        $this->authenticate($this->getAdminUser());
    }

    public function testThatWeCanFetchANewlyCreatedProductFromTheUI(): void
    {
        $identifier = 'new_product';
        $this->createProductWithInternalApi($identifier);

        $admin = $this->getAdminUser();
        $pqb = $this->pqbFactory
            ->create(['limit' => 1])
            ->addFilter('identifier', Operators::EQUALS, $identifier);

        $createdProduct = $this->getConnectorProductsQuery
            ->fromProductQueryBuilder($pqb, $admin->getId(), null, null, null)
            ->connectorProducts();

        $this->assertCount(1, $createdProduct);
        $this->assertContainsOnlyInstancesOf(ConnectorProduct::class, $createdProduct);
        $this->assertEquals($identifier, $createdProduct[0]->identifier());
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
        return self::$container->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }
}
