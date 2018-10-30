<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\AttributeGroup\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group ce
 */
class CreateAttributeGroupEndToEnd extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenAnAttributeGroupIsCreated()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"technical"
    }
JSON;

        $client->request('POST', 'api/rest/v1/attribute-groups', [], [], [], $data);

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
        "code":"marketing"
    }
JSON;

        $client->request('POST', 'api/rest/v1/attribute-groups', [], [], [], $data);

        $attributeGroup = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier('marketing');

        $attributeGroupStandard = [
            'code'       => 'marketing',
            'sort_order' => 0,
            'attributes' => [],
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_group');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($attributeGroupStandard, $normalizer->normalize($attributeGroup));
    }

    public function testCompleteAttributeGroupCreation()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"manufacturing",
        "sort_order": 6,
        "attributes": [
            "sku",
            "a_date",
            "a_file"
        ],
        "labels": {
            "en_US": "Manufacturing",
            "fr_FR": "Fabrication"
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/attribute-groups', [], [], [], $data);

        $attributeGroup = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier('manufacturing');

        $attributeGroupStandard = [
            'code'       => 'manufacturing',
            'sort_order' => 6,
            'attributes' => ['sku', 'a_date', 'a_file'],
            'labels'     => [
                'en_US' => 'Manufacturing',
                'fr_FR' => 'Fabrication',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_group');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($attributeGroupStandard, $normalizer->normalize($attributeGroup));
    }

    public function testAttributeMoveOnAttributeGroupCreation()
    {
        $client = $this->createAuthenticatedClient();
        $attribute =
<<<JSON
    {
        "code":"an_attr_text",
        "type":"pim_catalog_text",
        "group":"attributeGroupA"
    }
JSON;
        $client->request('POST', '/api/rest/v1/attributes', [], [], [], $attribute);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $attributeGroup =
<<<JSON
    {
        "code":"new_group",
        "sort_order": 42,
        "attributes": [
            "an_attr_text"
        ]
    }
JSON;
        $client->request('POST', '/api/rest/v1/attribute-groups', [], [], [], $attributeGroup);
        $responseAttributeGroup = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $responseAttributeGroup->getStatusCode());

        $client->request('GET', '/api/rest/v1/attributes/an_attr_text');

        $responseGroup = $client->getResponse();
        $expectedAttribute =
<<<JSON
{
    "code": "an_attr_text",
    "type": "pim_catalog_text",
    "group": "new_group",
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

    public function testAttributeGroupCreationWithInvalidSortOrder()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"invalid_sort_order",
        "sort_order": "foo",
        "labels": {
            "en_US": null,
            "fr_FR": ""
        }
    }
JSON;

        $expectedContent =
<<<JSON
{
	"code": 422,
	"message": "Validation failed.",
	"errors": [{
	    "property": "sort_order",
	    "message": "This value should be of type numeric."
	}]
}
JSON;

        $client->request('POST', 'api/rest/v1/attribute-groups', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testAttributeGroupCreationWithEmptyLabels()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"empty_label_attribute_group",
        "sort_order": 7,
        "labels": {
            "en_US": null,
            "fr_FR": ""
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/attribute-groups', [], [], [], $data);

        $attributeGroup = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier('empty_label_attribute_group');

        $attributeGroupStandard = [
            'code'       => 'empty_label_attribute_group',
            'sort_order' => 7,
            'attributes' => [],
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_group');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($attributeGroupStandard, $normalizer->normalize($attributeGroup));
    }

    public function testResponseWhenContentIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', 'api/rest/v1/attribute-groups', [], [], [], '');

        $expectedContent =
<<<JSON
{
	"code": 400,
	"message": "Invalid json message received"
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenContentIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', 'api/rest/v1/attribute-groups', [], [], [], '{');

        $expectedContent =
<<<JSON
{
	"code": 400,
	"message": "Invalid json message received"
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenAttributeGroupCodeAlreadyExists()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"attributeGroupA"
    }
JSON;

        $expectedContent =
<<<JSON
{
	"code": 422,
	"message": "Validation failed.",
	"errors": [{
	    "property": "code",
	    "message": "This value is already used."
	}]
}
JSON;

        $client->request('POST', 'api/rest/v1/attribute-groups', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenAttributeGroupCodeIsNotScalar()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":[]
    }
JSON;

        $expectedContent =
<<<JSON
{
	"code": 422,
	"message": "Property \"code\" expects a scalar as data, \"array\" given. Check the expected format on the API documentation.",
	"_links": {
	    "documentation": {
	        "href": "http://api.akeneo.com/api-reference.html#post_attribute_groups"
	    }
	}
}
JSON;

        $client->request('POST', 'api/rest/v1/attribute-groups', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenAPropertyIsNotExpected()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"new_attribute_group",
        "extra_property": ""
    }
JSON;

        $expectedContent =
<<<JSON
{
	"code": 422,
	"message": "Property \"extra_property\" does not exist. Check the expected format on the API documentation.",
	"_links": {
	    "documentation": {
	        "href": "http://api.akeneo.com/api-reference.html#post_attribute_groups"
	    }
	}
}
JSON;

        $client->request('POST', 'api/rest/v1/attribute-groups', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenArrayExpectedValueHasAnInvalidStructure()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "labels":{
            "en_US": []
        }
    }
JSON;

        $expectedContent =
<<<JSON
{
	"code": 422,
	"message": "Property \"labels\" expects an array with valid data, one of the \"labels\" values is not a scalar. Check the expected format on the API documentation.",
	"_links": {
	    "documentation": {
	        "href": "http://api.akeneo.com/api-reference.html#post_attribute_groups"
	    }
	}
}
JSON;

        $client->request('POST', 'api/rest/v1/attribute-groups', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenLabelsIsNull()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "labels":null
    }
JSON;

        $client->request('POST', 'api/rest/v1/attribute-groups', [], [], [], $data);

        $response = $client->getResponse();
        $expectedContent =
<<<JSON
{
	"code": 422,
	"message": "Property \"labels\" expects an array as data, \"NULL\" given. Check the expected format on the API documentation.",
	"_links": {
	    "documentation": {
	        "href": "http://api.akeneo.com/api-reference.html#post_attribute_groups"
	    }
	}
}
JSON;

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenAttributesIsNull()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "attributes":null
    }
JSON;

        $client->request('POST', 'api/rest/v1/attribute-groups', [], [], [], $data);

        $response = $client->getResponse();
        $expectedContent =
<<<JSON
{
	"code": 422,
	"message": "Property \"attributes\" expects an array as data, \"NULL\" given. Check the expected format on the API documentation.",
	"_links": {
	    "documentation": {
	        "href": "http://api.akeneo.com/api-reference.html#post_attribute_groups"
	    }
	}
}
JSON;
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenLocaleCodeInLabelsIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"unknown_locale",
        "labels": {
            "":"label"
        }
    }
JSON;

        $expectedContent =
<<<JSON
{
	"code": 422,
	"message": "Validation failed.",
	"errors": [{
	     "property": "labels",
	     "message": "The locale \"\" does not exist."
	}]
}
JSON;

        $client->request('POST', 'api/rest/v1/attribute-groups', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenLocaleCodeDoesNotExist()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code":"unknown_locale",
        "type":"pim_catalog_text",
        "group":"attributeGroupA",
        "labels": {
            "foo": "label"
        }
    }
JSON;

        $expectedContent =
<<<JSON
{
	"code": 422,
	"message": "Validation failed.",
	"errors": [{
	     "property": "labels",
	     "message": "The locale \"foo\" does not exist."
	}]
}
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

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
