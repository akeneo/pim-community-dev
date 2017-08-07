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
}
