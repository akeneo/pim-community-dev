<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\ProductDraft;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateProductDraftByUuidEndToEnd extends AbstractProductTestCase
{
    private string $originalProductUuid;

    public function setUp(): void
    {
        parent::setUp();
        $this->get('feature_flags')->enable('proposal');

        $product = $this->createProduct('product_draft_for_redactor', [
            new SetCategories(['categoryA']),
            new SetTextValue('a_text', null, null, 'a text'),
        ]);
        $this->createEntityWithValuesDraft('mary', $product, [
            'values' => [
                'a_simple_select' => [
                    ['data' => 'optionA', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
        $this->originalProductUuid = $product->getUuid()->toString();
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
        $client->request('PATCH', 'api/rest/v1/products-uuid/' . $this->originalProductUuid, [], [], [], $data);

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

    public function testSuccessfulToUpdateADraft()
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
        $client->request('PATCH', 'api/rest/v1/products-uuid/' . $this->originalProductUuid, [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->assertSame(
            \sprintf('http://localhost/api/rest/v1/products-uuid/%s/draft', $this->originalProductUuid),
            $response->headers->get('location')
        );

        $product = $this->get('pim_catalog.repository.product')->find($this->originalProductUuid);
        $this->assertSame('a text', $product->getValue('a_text')->getData());

        $productDraft = $this->get('pimee_workflow.repository.product_draft')->findUserEntityWithValuesDraft($product, 'mary');
        $this->assertNotNull($productDraft);

        $expected = <<<JSON
{
    "values":{
        "a_text":[
            {"locale":null,"scope":null,"data":"the text"}
        ],
        "a_simple_select": [
            {"data":"optionA", "locale":null, "scope":null}
        ]
    },
    "review_statuses":{
        "a_text":[
            {"locale":null,"scope":null,"status":"draft"}
        ],
        "a_simple_select":[
            {"locale":null,"scope":null,"status":"draft"}
        ]
    }
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, json_encode($productDraft->getChanges()));
    }
}
