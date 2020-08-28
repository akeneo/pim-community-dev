<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\Update;

use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateProductEndToEnd extends AbstractProductTestCase
{
    public function testSuccessfullyUpdateProductWithDataFromTheGet()
    {
        $getClient = $this->createAuthenticatedClient();
        $getClient->request('GET', 'api/rest/v1/products/product_viewable_by_everybody_1');

        $getResponse = $getClient->getResponse();
        $getContent = $getResponse->getContent();
        $data = json_decode($getContent, true);
        $data['family'] = 'familyA';

        $patchClient = $this->createAuthenticatedClient();
        $patchClient->request('PATCH', 'api/rest/v1/products/product_viewable_by_everybody_1', [], [], [], json_encode($data));
        $patchResponse = $patchClient->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $patchResponse->getStatusCode());
    }

    public function testFailedToUpdateProductNotViewableByUser()
    {
        $expectedResponseContent =
            <<<JSON
{"code":404,"message":"Product \"product_not_viewable_by_redactor\" does not exist or you do not have permission to access it."}
JSON;
        $data = <<<JSON
{
    "values": {
        "a_localized_and_scopable_text_area": [
            {
                "data": "Awesome Data !",
                "locale": "en_US",
                "scope": "ecommerce"
            }
        ]
    }
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/product_not_viewable_by_redactor', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testSuccessfullyToUpdateAProduct()
    {
        $data = <<<JSON
{
    "values": {
        "a_text": [
            { "data": "the text", "locale": null, "scope": null }
        ]
    }
}
JSON;
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/product_without_category', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame('http://localhost/api/rest/v1/products/product_without_category', $response->headers->get('location'));

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_without_category');
        $this->assertSame('the text', $product->getValue('a_text')->getData());
    }
}
