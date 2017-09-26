<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Response;

class DeleteVariantProductIntegration extends AbstractProductTestCase
{
    public function testDeleteAVariantProduct()
    {
        $client = $this->createAuthenticatedClient();

        $this->assertCount(246, $this->get('pim_catalog.repository.product')->findAll());

        $bikerJacketLeatherXxs = $this->get('pim_catalog.repository.product')->findOneByIdentifier('biker-jacket-leather-xxs');
        $this->get('pim_catalog.elasticsearch.indexer.product')->index($bikerJacketLeatherXxs);
        $client->request('DELETE', 'api/rest/v1/products/biker-jacket-leather-xxs');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->assertCount(245, $this->get('pim_catalog.repository.product')->findAll());
        $this->assertNull($this->get('pim_catalog.repository.product')->findOneByIdentifier('biker-jacket-leather-xxs'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return new Configuration([Configuration::getFunctionalCatalogPath('catalog_modeling')]);
    }
}
