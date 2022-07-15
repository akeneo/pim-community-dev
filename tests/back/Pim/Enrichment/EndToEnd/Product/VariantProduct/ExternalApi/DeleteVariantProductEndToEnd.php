<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\VariantProduct\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class DeleteVariantProductEndToEnd extends AbstractProductTestCase
{
    use AssertEventCountTrait;

    public function testDeleteAVariantProduct()
    {
        $client = $this->createAuthenticatedClient();

        $this->assertCount(242, $this->get('pim_catalog.repository.product')->findAll());

        $bikerJacketLeatherXxs = $this->get('pim_catalog.repository.product')->findOneByIdentifier('biker-jacket-leather-xxs');
        $this->get('pim_catalog.elasticsearch.indexer.product')->indexFromProductUuids([
            $bikerJacketLeatherXxs->getUuid()
        ]);
        $client->request('DELETE', 'api/rest/v1/products/biker-jacket-leather-xxs');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->assertCount(241, $this->get('pim_catalog.repository.product')->findAll());
        $this->assertNull($this->get('pim_catalog.repository.product')->findOneByIdentifier('biker-jacket-leather-xxs'));

        $this->assertEventCount(1, ProductRemoved::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
