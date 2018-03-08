<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\Create;

use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class AddAssociationOnProductWithPermissionsIntegration extends AbstractProductTestCase
{
    public function testErrorProductWithNotGrantedAssociatedProduct()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "identifier": "my_product",
    "associations": {
        "X_SELL": {
            "products": ["product_not_viewable_by_redactor"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('{"code":422,"message":"Property \"associations\" expects a valid product identifier. The product does not exist, \"product_not_viewable_by_redactor\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#post_products"}}}', $response->getContent());
    }

    public function testSuccessProductWithGrantedAssociatedProductForManager()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $data = <<<JSON
{
    "identifier": "my_product",
    "associations": {
        "X_SELL": {
            "products": ["product_not_viewable_by_redactor"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testSuccessProductWithViewableAssociatedProduct()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "identifier": "my_product",
    "associations": {
        "X_SELL": {
            "products": ["product_without_category","product_viewable_by_everybody_2"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
    }
}
