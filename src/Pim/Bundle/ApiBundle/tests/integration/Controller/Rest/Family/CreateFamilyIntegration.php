<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Rest\Family;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Bundle\CatalogBundle\Version;
use Symfony\Component\HttpFoundation\Response;

class CreateFamilyIntegration extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenAFamilyIsCreated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "new_family_headers"
    }
JSON;

        $client->request('POST', 'api/rest/v1/families', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/families/new_family_headers', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    public function testFormatStandardWhenAFamilyIsCreatedButIncompleted()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "new_family_incompleted"
    }
JSON;

        $client->request('POST', 'api/rest/v1/families', [], [], [], $data);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('new_family_incompleted');
        $familyStandard = [
            'code'                   => 'new_family_incompleted',
            'attributes'             => ['sku'],
            'attribute_as_label'     => 'sku',
            'attribute_requirements' => [
                'ecommerce'       => ['sku'],
                'ecommerce_china' => ['sku'],
                'tablet'          => ['sku'],
            ],
            'labels'                 => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.family');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($familyStandard, $normalizer->normalize($family));
    }

    public function testCompleteFamilyCreation()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "complete_family_creation",
        "attributes": ["an_image", "a_metric", "a_price"],
        "attribute_as_label": "sku",
        "attribute_requirements": {
            "ecommerce": ["sku", "a_metric"],
            "tablet": ["sku", "a_price"]
        },
        "labels": {
            "en_US": "Complete Family creation",
            "fr_FR": "Création complète famille"
        }
    }
JSON;
        $client->request('POST', 'api/rest/v1/families', [], [], [], $data);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('complete_family_creation');
        $familyStandard = [
            'code'                   => 'complete_family_creation',
            'attributes'             => ['a_metric', 'a_price', 'an_image', 'sku'],
            'attribute_as_label'     => 'sku',
            'attribute_requirements' => [
                'ecommerce'       => ['a_metric', 'sku'],
                'ecommerce_china' => ['sku'],
                'tablet'          => ['a_price', 'sku'],
            ],
            'labels'                 => [
                'en_US' => 'Complete Family creation',
                'fr_FR' => 'Création complète famille',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.family');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($familyStandard, $normalizer->normalize($family));
    }

    public function testResponseWhenContentIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data = '';

        $expectedContent = [
            'code'    => 400,
            'message' => 'Invalid json message received',
        ];

        $client->request('POST', 'api/rest/v1/families', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenContentIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{';

        $expectedContent = [
            'code'    => 400,
            'message' => 'Invalid json message received',
        ];

        $client->request('POST', 'api/rest/v1/families', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenFamilyCodeAlreadyExists()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "familyA"
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'code',
                    'message' => 'This value is already used.',
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/families', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenValidationFailed()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": ""
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'code',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/families', [], [], [], $data);

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
        "code": "new_family",
        "extra_property": ""
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "extra_property" does not exist. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#family', $version),
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/families', [], [], [], $data);

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
        "labels": null
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "labels" expects an array as data, "NULL" given. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#family', $version),
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/families', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenLocaleCodeInLabelsIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "test_empty_locale",
        "labels": {
            "" : "label"
         }
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'labels',
                    'message' => 'The locale "" does not exist.',
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/families', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenLocaleCodeDoesNotExist()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "test_unknown_localee",
        "labels": {
            "foo" : "label"
         }
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'labels',
                    'message' => 'The locale "foo" does not exist.',
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/families', [], [], [], $data);

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
