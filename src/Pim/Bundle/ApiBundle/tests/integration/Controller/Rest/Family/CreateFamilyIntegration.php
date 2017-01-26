<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Rest\Family;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Bundle\CatalogBundle\Version;
use Symfony\Component\HttpFoundation\Response;

class CreateFamilyIntegration extends TestCase
{
    public function testHttpHeadersInResponseWhenAFamilyIsCreated()
    {
        $client = static::createClient();
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

    public function testFormatStandardWhenAFamilyIsCreatedButUncompleted()
    {
        $client = static::createClient();
        $data =
<<<JSON
    {
        "code": "new_family_uncompleted"
    }
JSON;

        $client->request('POST', 'api/rest/v1/families', [], [], [], $data);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('new_family_uncompleted');
        $familyStandard = [
            'code'                   => 'new_family_uncompleted',
            'attributes'             => ['sku'],
            'attribute_as_label'     => 'sku',
            'attribute_requirements' => [
                'ecommerce' => ['sku'],
                'tablet'    => ['sku'],
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
        $client = static::createClient();
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
                'ecommerce' => ['a_metric', 'sku'],
                'tablet'    => ['a_price', 'sku'],
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

    public function testResponseWhenContentIsNotValid()
    {
        $client = static::createClient();
        $data = '';

        $expectedContent = [
            'code'    => 400,
            'message' => 'JSON is not valid.',
        ];

        $client->request('POST', 'api/rest/v1/families', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenFamilyCodeAlreadyExists()
    {
        $client = static::createClient();
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
        $client = static::createClient();
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
        $client = static::createClient();
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
        $client = static::createClient();
        $data =
<<<JSON
    {
        "labels": null
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "labels" expects an array (for update family). Check the standard format documentation.',
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
