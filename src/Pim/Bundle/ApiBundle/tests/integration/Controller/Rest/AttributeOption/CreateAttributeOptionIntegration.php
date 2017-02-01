<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Rest\AttributeOption;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Bundle\CatalogBundle\Version;
use Symfony\Component\HttpFoundation\Response;

class CreateAttributeOptionIntegration extends ApiTestCase
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

    public function testFormatStandardWhenAnAttributeOptionIsCreatedButUncompleted()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"optionD",
        "attribute":"a_multi_select",
        "labels":[]
    }
JSON;
        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);

        $attributeOption = $this->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier('a_multi_select' . '.' . 'optionD');

        $attributeOptionStandard = [
            'code'       => 'optionD',
            'attribute'  => 'a_multi_select',
            'sort_order' => 1,
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
            ->findOneByIdentifier('a_multi_select' . '.' . 'optionE');

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

    public function testResponseWhenContentIsInvalid()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{';

        $expectedContent = [
            'code'    => 400,
            'message' => 'Invalid json message received',
        ];

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenContentIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data = '';

        $expectedContent = [
            'code'    => 400,
            'message' => 'Invalid json message received',
        ];

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenJsonIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'code',
                    'message' => 'This value should not be blank.',
                ],
                [
                    'field'   => 'attribute',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
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

        $expectedContent = [
            'code'    => 404,
            'message' => 'Attribute "an_unknown_multi_select" does not exist.',
        ];

        $client->request('POST', 'api/rest/v1/attributes/an_unknown_multi_select/options', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
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
        $expectedContent = [
            'code'    => 404,
            'message' => 'Attribute "sku" does not support options. Only attributes of type "pim_catalog_simpleselect", "pim_catalog_multiselect" support options.',
        ];

        $client->request('POST', 'api/rest/v1/attributes/sku/options', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenAttributeInUriIsNotIdenticalAsInRequestBody()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"optionE",
        "attribute":"a_multi_sel"
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Attribute code "a_multi_sel" in the request body must match "a_multi_select" in the URI.',
        ];

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
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

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "extra_property" does not exist. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute-option', $version),
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
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

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "labels" expects an array. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute-option', $version),
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenAttributeIsNotInRequestBody()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"optionD",
        "labels":[],
        "sort_order": 1
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'attribute',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            false
        );
    }
}
