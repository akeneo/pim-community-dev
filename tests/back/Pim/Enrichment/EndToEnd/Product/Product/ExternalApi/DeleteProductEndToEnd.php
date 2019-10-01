<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Response;

class DeleteProductEndToEnd extends AbstractProductTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalSqlCatalog();
    }

    public function testDeleteAProduct()
    {
        $this->createAdminUser();
        $client = $this->createAuthenticatedClient();

        $this->assertCount(7, $this->get('pim_catalog.repository.product')->findAll());

        $this->get('pim_catalog.elasticsearch.indexer.product')->indexFromProductIdentifier('foo');
        $client->request('DELETE', 'api/rest/v1/products/foo');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->assertCount(6, $this->get('pim_catalog.repository.product')->findAll());
        $this->assertNull($this->get('pim_catalog.repository.product')->findOneByIdentifier('foo'));
    }

    public function testNotFoundAProduct()
    {
        $this->createAdminUser();
        $client = $this->createAuthenticatedClient();

        $client->request('DELETE', 'api/rest/v1/products/not_found');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('Product "not_found" does not exist.', $content['message']);
    }
}
