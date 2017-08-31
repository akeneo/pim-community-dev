<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\Update;

use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateProductIntegration extends AbstractProductTestCase
{
    public function testFailedToUpdateProductNotViewableByUser()
    {
        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You can neither view, nor update, nor delete the product \"product_not_viewable_by_redactor\", as it is only categorized in categories on which you do not have a view permission."}
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
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
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
