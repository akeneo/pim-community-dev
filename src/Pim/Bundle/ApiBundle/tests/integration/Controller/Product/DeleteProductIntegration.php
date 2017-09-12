<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Response;

class DeleteProductIntegration extends AbstractProductTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalSqlCatalogPath()]);
    }

    public function testDeleteAProduct()
    {
        $client = $this->createAuthenticatedClient();

        $this->assertCount(4, $this->get('pim_catalog.repository.product')->findAll());

        $fooProduct = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');
        $this->get('pim_catalog.elasticsearch.indexer.product')->index($fooProduct);
        $client->request('DELETE', 'api/rest/v1/products/foo');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->assertCount(3, $this->get('pim_catalog.repository.product')->findAll());
        $this->assertNull($this->get('pim_catalog.repository.product')->findOneByIdentifier('foo'));
    }

    public function testNotFoundAProduct()
    {
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
