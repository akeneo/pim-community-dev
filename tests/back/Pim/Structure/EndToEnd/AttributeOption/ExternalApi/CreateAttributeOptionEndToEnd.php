<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\AttributeOption\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateAttributeOptionEndToEnd extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenAnAttributeOptionIsCreated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"optionC",
        "attribute":"a_multi_select",
        "sort_order":30,
        "labels":{"en_US":"Option C"}
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/attributes/a_multi_select/options/optionC',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
    }

    public function testStandardFormatWhenAnAttributeOptionIsCreatedButIncompleted()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"optionD",
        "attribute":"a_multi_select",
        "labels":{}
    }
JSON;
        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);

        $attributeOption = $this->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('a_multi_select.optionD');

        $attributeOptionStandard = [
            'code'       => 'optionD',
            'attribute'  => 'a_multi_select',
            'sort_order' => 21,
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_option');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($attributeOptionStandard, $normalizer->normalize($attributeOption));
    }

    public function testCompleteAttributeOptionCreation()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"optionE",
        "attribute":"a_multi_select",
        "sort_order":30,
        "labels":{"en_US":"Option E"}
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);

        $attributeOption = $this->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('a_multi_select.optionE');

        $attributeOptionStandard = [
            'code'       => 'optionE',
            'attribute'  => 'a_multi_select',
            'sort_order' => 30,
            'labels'     => [
                'en_US' => 'Option E',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_option');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($attributeOptionStandard, $normalizer->normalize($attributeOption));
    }

    public function testStandardFormatWithoutAttributeCodeInBody()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"optionF"
    }
JSON;
        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);

        $attributeOption = $this->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('a_multi_select.optionF');

        $attributeOptionStandard = [
            'code'       => 'optionF',
            'attribute'  => 'a_multi_select',
            'sort_order' => 21,
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_option');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($attributeOptionStandard, $normalizer->normalize($attributeOption));
    }

    public function testAttributeOptionCreationWithEmptyLabels()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"empty_labels",
        "attribute":"a_multi_select",
        "sort_order":30,
        "labels":{
            "en_US": null,
            "fr_FR": ""
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);

        $attributeOption = $this->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('a_multi_select.empty_labels');

        $attributeOptionStandard = [
            'code'       => 'empty_labels',
            'attribute'  => 'a_multi_select',
            'sort_order' => 30,
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_option');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($attributeOptionStandard, $normalizer->normalize($attributeOption));
    }


    public function testResponseWhenContentIsInvalid()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{';

        $expectedContent =
<<<JSON
    {
        "message" : "Invalid json message received",
        "code" : 400
    }
JSON;


        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testResponseWhenContentIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data = '';

        $expectedContent =
<<<JSON
    {
        "message" : "Invalid json message received",
        "code" : 400
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testResponseWhenJsonIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $expectedContent =
<<<JSON
    {
    	"message": "Validation failed.",
    	"errors": [{
    		"property": "code",
    		"message": "This value should not be blank."
    	}],
    	"code": 422
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenAttributeDoesNotExist()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "attribute":"an_unknown_multi_select"
    }
JSON;

        $expectedContent =
<<<JSON
    {
        "message" : "Attribute \"an_unknown_multi_select\" does not exist.",
        "code" : 404
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes/an_unknown_multi_select/options', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testResponseWhenAttributeDoesNotSupportOptions()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"optionA",
        "attribute":"sku"
    }
JSON;

        $expectedContent =
<<<JSON
    {
        "code": 422,
        "message": "Validation failed.",
        "errors": [{
            "property": "attribute",
            "message": "Attribute \"sku\" does not support options. Only attributes of type \"pim_catalog_simpleselect\", \"pim_catalog_multiselect\" support options"
        }]
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes/sku/options', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenAttributeInUriIsNotIdenticalToAttributeInBody()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"optionE",
        "attribute":"a_multi_sel"
    }
JSON;

        $expectedContent =
<<<JSON
    {
        "message" : "The attribute code \"a_multi_sel\" provided in the request body must match the attribute code \"a_multi_select\" provided in the url.",
        "code" : 422
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenAPropertyIsNotExpected()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "attribute":"a_multi_select",
        "extra_property": null
    }
JSON;

        $expectedContent =
<<<JSON
    {
    	"message": "Property \"extra_property\" does not exist. Check the expected format on the API documentation.",
    	"_links": {
    		"documentation": {
    			"href": "http://api.akeneo.com/api-reference.html#post_attributes__attribute_code__options"
    		}
    	},
    	"code": 422
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenLabelsIsNull()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "attribute":"a_multi_select",
        "labels": null
    }
JSON;

        $expectedContent =
<<<JSON
    {
        "code": 422,
        "message": "Property \"labels\" expects an array as data, \"NULL\" given. Check the expected format on the API documentation.",
        "_links": {
            "documentation": {
                 "href": "http://api.akeneo.com/api-reference.html#post_attributes__attribute_code__options"
            }
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);

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
