<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi;

use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class DeleteProductModelEndToEnd extends AbstractProductModelTestCase
{
    /**
     * @test
     */
    public function it_successfully_deletes_a_product_model(): void
    {
        $productModelCode = reset($this->productModelCodes);

        $client = $this->createAuthenticatedClient();

        $client->request('DELETE', 'api/rest/v1/product-models/' . $productModelCode);
        $response = $client->getResponse();
        Assert::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $client->request('DELETE', 'api/rest/v1/product-models/' . $productModelCode);
        $response = $client->getResponse();
        Assert::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
