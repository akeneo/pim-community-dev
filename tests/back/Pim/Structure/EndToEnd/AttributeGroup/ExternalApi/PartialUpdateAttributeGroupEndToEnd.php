<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\AttributeGroup\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group ce
 */
class PartialUpdateAttributeGroupEndToEnd extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenAnAttributeGroupIsUpdated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "attributeGroupA"
    }
JSON;

        $client->request('PATCH', '/api/rest/v1/attribute-groups/attributeGroupA', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame('http://localhost/api/rest/v1/attribute-groups/attributeGroupA', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    public function testHttpHeadersInResponseWhenAnAttributeGroupIsCreated()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"technical"
    }
JSON;

        $client->request('PATCH', '/api/rest/v1/attribute-groups/technical', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/attribute-groups/technical', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    public function testStandardFormatWhenAnAttributeGroupIsCreatedButIncomplete()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"technical"
    }
JSON;
        $client->request('PATCH', '/api/rest/v1/attribute-groups/technical', [], [], [], $data);

        $attributeGroup = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier('technical');
        $attributeGroupStandard = [
            'code'       => 'technical',
            'sort_order' => 0,
            'attributes' => [],
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_group');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($attributeGroupStandard, $normalizer->normalize($attributeGroup));
    }

    public function testResponseWhenAnAttributeGroupIsCreatedWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', '/api/rest/v1/attribute-groups/void', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }

    public function testCompleteAttributeGroupCreationWithCodeProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
{
    "code": "specification"
}
JSON;

        $client->request('PATCH', '/api/rest/v1/attribute-groups/specification', [], [], [], $data);

        $attributeGroup = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier('specification');
        $attributeGroupStandard = [
            'code'       => 'specification',
            'sort_order' => 0,
            'attributes' => [],
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_group');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($attributeGroupStandard, $normalizer->normalize($attributeGroup));
    }

    public function testCompleteAttributeGroupCreationWithoutCodeProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', '/api/rest/v1/attribute-groups/design', [], [], [], $data);

        $attributeGroup = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier('design');
        $attributeGroupStandard = [
            'code'       => 'design',
            'sort_order' => 0,
            'attributes' => [],
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_group');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($attributeGroupStandard, $normalizer->normalize($attributeGroup));
    }

    public function testAttributeGroupPartialUpdateWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', '/api/rest/v1/attribute-groups/attributeGroupA', [], [], [], $data);

        $attributeGroup = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier('attributeGroupA');
        $attributeGroupStandard = [
            'code'       => 'attributeGroupA',
            'sort_order' => 1,
            'attributes' => [
                'sku',
                'a_date',
                'a_file',
                'an_image',
                'a_price',
                'a_price_without_decimal',
                'a_ref_data_multi_select',
                'a_ref_data_simple_select',
                'a_text',
                'a_regexp',
                'a_text_area',
                'a_yes_no',
                'a_scopable_price',
                'a_localized_and_scopable_text_area',
            ],
            'labels'     => [
                'en_US' => 'Attribute group A',
                'fr_FR' => "Groupe d'attribut A",
            ],
        ];

        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_group');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($attributeGroupStandard, $normalizer->normalize($attributeGroup));
    }

    public function testAttributeGroupPartialUpdateWithEmptyLabels()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
{
    "labels": {
        "en_US": ""
    }
}        
JSON;
        $client->request('PATCH', '/api/rest/v1/attribute-groups/attributeGroupA', [], [], [], $data);

        $attributeGroup = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier('attributeGroupA');
        $attributeGroupStandard = [
            'code'       => 'attributeGroupA',
            'sort_order' => 1,
            'attributes' => [
                'sku',
                'a_date',
                'a_file',
                'an_image',
                'a_price',
                'a_price_without_decimal',
                'a_ref_data_multi_select',
                'a_ref_data_simple_select',
                'a_text',
                'a_regexp',
                'a_text_area',
                'a_yes_no',
                'a_scopable_price',
                'a_localized_and_scopable_text_area',
            ],
            'labels'     => [
                "fr_FR" => "Groupe d'attribut A",
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_group');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($attributeGroupStandard, $normalizer->normalize($attributeGroup));
    }

    public function testResponseWhenContentIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data = '';

        $expectedContent =
<<<JSON
{
    "code":400,
    "message": "Invalid json message received"
}
JSON;

        $client->request('PATCH', '/api/rest/v1/attribute-groups/attributeGroupA', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenAnAttributeIsCreatedWithInconsistentCodes()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "inconsistent_code2"
    }
JSON;

        $expectedContent =
<<<JSON
{
    "code":422,
    "message": "The code \"inconsistent_code2\" provided in the request body must match the code \"inconsistent_code1\" provided in the url."
}
JSON;
        $client->request('PATCH', '/api/rest/v1/attribute-groups/inconsistent_code1', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testAttributeMoveOnAttributeGroupPartialUpdate()
    {
        $client = $this->createAuthenticatedClient();
        $attribute =
<<<JSON
    {
        "code":"an_attr_text",
        "type":"pim_catalog_text",
        "group":"other"
    }
JSON;
        $client->request('POST', '/api/rest/v1/attributes', [], [], [], $attribute);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $attributeGroup =
<<<JSON
    {
        "code":"attributeGroupA",
        "sort_order": 42,
        "attributes": [
            "an_attr_text"
        ]
    }
JSON;
        $client->request('PATCH', '/api/rest/v1/attribute-groups/attributeGroupA', [], [], [], $attributeGroup);
        $responseAttributeGroup = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $responseAttributeGroup->getStatusCode());

        $client->request('GET', '/api/rest/v1/attributes/an_attr_text');

        $responseGroup = $client->getResponse();
        $expectedAttribute =
<<<JSON
{
    "code": "an_attr_text",
    "type": "pim_catalog_text",
    "group": "attributeGroupA",
    "unique": false,
    "useable_as_grid_filter": false,
    "allowed_extensions": [],
    "metric_family": null,
    "default_metric_unit": null,
    "reference_data_name": null,
    "available_locales": [],
    "max_characters": null,
    "validation_rule": null,
    "validation_regexp": null,
    "wysiwyg_enabled": null,
    "number_min": null,
    "number_max": null,
    "decimals_allowed": null,
    "negative_allowed": null,
    "date_min": null,
    "date_max": null,
    "max_file_size": null,
    "minimum_input_length": null,
    "sort_order": 0,
    "localizable": false,
    "scopable": false,
    "labels": {},
    "auto_option_sorting": null
}
JSON;

        $this->assertJsonStringEqualsJsonString($expectedAttribute, $responseGroup->getContent());
    }

    public function testResponseWhenTryingToAddANegativeSortOrder()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "attributeGroupA",
        "sort_order": -1
    }
JSON;
        $client->request('PATCH', '/api/rest/v1/attribute-groups/attributeGroupA', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code":422,
    "message": "Validation failed.",
    "errors": [
        {
            "property": "sort_order",
            "message": "This value should be greater than or equal to 0."
        }
    ]
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
