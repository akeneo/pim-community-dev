<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class DeleteProductWithUuidEndToEnd extends AbstractProductTestCase
{
    use AssertEventCountTrait;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalSqlCatalog();
    }

    public function testDeleteAProductByUuid()
    {
        $this->createAdminUser();
        $client = $this->createAuthenticatedClient();

        $testProduct = $this->createProduct('test_uuid', []);
        $this->assertCount(8, $this->get('pim_catalog.repository.product')->findAll());

        $this->get('pim_catalog.elasticsearch.indexer.product')->indexFromProductUuids([$testProduct->getUuid()]);
        $client->request('DELETE', 'api/rest/v1/products-uuid/' . $testProduct->getUuid()->toString());

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->assertCount(7, $this->get('pim_catalog.repository.product')->findAll());
        $this->assertNull($this->get('pim_catalog.repository.product')->findOneByIdentifier('test_uuid'));

        $this->assertEventCount(1, ProductRemoved::class);
    }

    public function testDeleteAProductByUppercaseUuid()
    {
        $this->createAdminUser();
        $client = $this->createAuthenticatedClient();

        $testProduct = $this->createProduct('test_uuid', []);
        $this->assertCount(8, $this->get('pim_catalog.repository.product')->findAll());

        $this->get('pim_catalog.elasticsearch.indexer.product')->indexFromProductUuids([$testProduct->getUuid()]);
        $client->request('DELETE', 'api/rest/v1/products-uuid/' . \strtoupper($testProduct->getUuid()->toString()));

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testProductUuidNotFound()
    {
        $this->createAdminUser();
        $client = $this->createAuthenticatedClient();

        $randomUuid = Uuid::uuid4()->toString();
        $client->request('DELETE', 'api/rest/v1/products-uuid/' . $randomUuid);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('The ' . $randomUuid . ' product does not exist in your PIM or you do not have permission to access it.', $content['message']);
    }

    public function testAccessDeniedWhenDeletingProductWithoutTheAcl()
    {
        $this->createAdminUser();
        $client = $this->createAuthenticatedClient();
        $this->removeAclFromRole('action:pim_api_product_remove');

        $randomUuid = Uuid::uuid4()->toString();
        $client->request('DELETE', 'api/rest/v1/products-uuid/' . $randomUuid);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }
}
