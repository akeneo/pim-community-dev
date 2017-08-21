<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\Update;

use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateProductDraftIntegration extends AbstractProductTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->createProduct('product_draft_for_redactor', [
            'categories' => ['categoryA'],
            'values'     => [
                'a_text' => [
                    ['data' => 'a text', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
    }

    public function testErrorWhenFieldsAreUpdatedOnUpdateADraft()
    {
        $data = <<<JSON
{
    "enabled": false,
    "groups": ["groupA"]
}
JSON;
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/product_draft_for_redactor', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 403,
    "message": "You cannot update the following fields \"enabled, groups\". You should at least own this product to do it."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }
}
