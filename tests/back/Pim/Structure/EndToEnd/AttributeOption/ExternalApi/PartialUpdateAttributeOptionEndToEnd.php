<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\AttributeOption\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateAttributeOptionEndToEnd extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenAnAttributeOptionIsUpdated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "optionA"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/optionA', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/attributes/a_multi_select/options/optionA', $response->headers->get('location'));
    }

    public function testHttpHeadersInResponseWhenAnAttributeOptionIsCreated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"newOption",
        "attribute":"a_multi_select",
        "sort_order":30,
        "labels":{"en_US":"new option"}
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/newOption', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/attributes/a_multi_select/options/newOption',
            $response->headers->get('location')
        );
    }

    public function testStandardFormatWhenAnAttributeOptionIsCreatedButIncomplete()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"newOption",
        "attribute":"a_multi_select"
    }
JSON;
        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/newOption', [], [], [], $data);

        $attributeOption = $this->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('a_multi_select.newOption');

        $attributeOptionStandard = [
            'code'       => 'newOption',
            'attribute'  => 'a_multi_select',
            'sort_order' => 21,
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_option');

        $response = $client->getResponse();

        $this->assertSame($attributeOptionStandard, $normalizer->normalize($attributeOption));
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testStandardFormatWhenAnAttributeOptionIsCreatedWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/newOption', [], [], [], $data);

        $attributeOption = $this->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('a_multi_select.newOption');

        $attributeOptionStandard = [
            'code'       => 'newOption',
            'attribute'  => 'a_multi_select',
            'sort_order' => 21,
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_option');

        $response = $client->getResponse();

        $this->assertSame($attributeOptionStandard, $normalizer->normalize($attributeOption));
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testStandardFormatWhenAnAttributeOptionIsCreatedWithOptionCode()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"newOption"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/newOption', [], [], [], $data);

        $attributeOption = $this->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('a_multi_select.newOption');

        $attributeOptionStandard = [
            'code'       => 'newOption',
            'attribute'  => 'a_multi_select',
            'sort_order' => 21,
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_option');

        $response = $client->getResponse();

        $this->assertSame($attributeOptionStandard, $normalizer->normalize($attributeOption));
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testStandardFormatWhenAnAttributeOptionIsCreatedWithAttributeCode()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "attribute":"a_multi_select"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/newOption', [], [], [], $data);

        $attributeOption = $this->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('a_multi_select.newOption');

        $attributeOptionStandard = [
            'code'       => 'newOption',
            'attribute'  => 'a_multi_select',
            'sort_order' => 21,
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_option');

        $response = $client->getResponse();

        $this->assertSame($attributeOptionStandard, $normalizer->normalize($attributeOption));
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
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

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/optionE', [], [], [], $data);

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

    public function testPartialUpdateWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/optionA', [], [], [], $data);

        $attributeOption = $this->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('a_multi_select.optionA');

        $attributeOptionStandard = [
            'code'       => 'optionA',
            'attribute'  => 'a_multi_select',
            'sort_order' => 10,
            'labels'     => [
                'en_US' => 'Option A',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_option');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($attributeOptionStandard, $normalizer->normalize($attributeOption));
    }

    public function testPartialUpdateWithAnAttributeCode()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "attribute":"a_multi_select",
        "sort_order":"100"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/optionA', [], [], [], $data);

        $attributeOption = $this->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('a_multi_select.optionA');

        $attributeOptionStandard = [
            'code'       => 'optionA',
            'attribute'  => 'a_multi_select',
            'sort_order' => 100,
            'labels'     => [
                'en_US' => 'Option A',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_option');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($attributeOptionStandard, $normalizer->normalize($attributeOption));
    }

    public function testPartialUpdateWithAnOptionCode()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"optionA",
        "labels":{"fr_FR":"Option A fr"}
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/optionA', [], [], [], $data);

        $attributeOption = $this->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('a_multi_select.optionA');

        $attributeOptionStandard = [
            'code'       => 'optionA',
            'attribute'  => 'a_multi_select',
            'sort_order' => 10,
            'labels'     => [
                'en_US' => 'Option A',
                'fr_FR' => 'Option A fr',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_option');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($attributeOptionStandard, $normalizer->normalize($attributeOption));
    }

    public function testPartialUpdateWithEmptyLabels()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "labels": {
            "en_US": null,
            "fr_FR": ""
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/optionA', [], [], [], $data);

        $attributeOption = $this->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('a_multi_select.optionA');

        $attributeOptionStandard = [
            'code'       => 'optionA',
            'attribute'  => 'a_multi_select',
            'sort_order' => 10,
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_option');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($attributeOptionStandard, $normalizer->normalize($attributeOption));
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

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/optionA', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
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

        $client->request('PATCH', 'api/rest/v1/attributes/an_unknown_multi_select/options/optionA', [], [], [], $data);
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

        $client->request('PATCH', 'api/rest/v1/attributes/sku/options/optionA', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testPartialUpdateOfTheAttributeCodeAndOptionCode()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"newOptionCode",
        "attribute":"a_simple_select"
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
    	},{
    		"property": "attribute",
    		"message": "This property cannot be changed."
    	}]
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/optionA', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testCreateWhenAttributeInUriIsNotIdenticalToAttributeInBody()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "attribute":"a_simple_select"
    }
JSON;

        $expectedContent =
<<<JSON
    {
      "message" : "The attribute code \"a_simple_select\" provided in the request body must match the attribute code \"a_multi_select\" provided in the url.",
      "code" : 422
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/newOption', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testCreateWhenCodeInUriIsNotIdenticalToCodeInBod()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"foo"
    }
JSON;

        $expectedContent =
<<<JSON
    {
      "message" : "The option code \"foo\" provided in the request body must match the option code \"newOption\" provided in the url.",
      "code" : 422
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/newOption', [], [], [], $data);
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
    			"href": "http://api.akeneo.com/api-reference.html#patch_attributes__attribute_code__options__code_"
    		}
    	},
    	"code": 422
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/optionA', [], [], [], $data);

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
                "href": "http://api.akeneo.com/api-reference.html#patch_attributes__attribute_code__options__code_"
            }
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/optionA', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResponseWhenLocaleDoesNotExist()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "labels": {
            "foo": "bar"
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

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options/optionA', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
