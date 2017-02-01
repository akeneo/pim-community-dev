<?php

namespace tests\integration\Pim\Bundle\ApiBundle\Controller\Rest\Family;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Bundle\CatalogBundle\Version;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateFamilyIntegration extends TestCase
{
    public function testHttpHeadersInResponseWhenAFamilyIsUpdated()
    {
        $client = static::createClient();
        $data =
<<<JSON
    {
        "attribute_as_label": "a_text"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/families/familyA1', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/families/familyA1', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    public function testHttpHeadersInResponseWhenAFamilyIsCreated()
    {
        $client = static::createClient();
        $data =
<<<JSON
    {
        "code": "new_family_headers"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/families/new_family_headers', [], [], [], $data);

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

        $client->request('PATCH', 'api/rest/v1/families/new_family_uncompleted', [], [], [], $data);

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

    public function testFormatStandardWhenAFamilyIsCreatedWithAnEmptyContent()
    {
        $client = static::createClient();
        $data = '{}';

        $client->request('PATCH', 'api/rest/v1/families/new_category_empty_content', [], [], [], $data);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('new_category_empty_content');
        $familyStandard = [
            'code'                   => 'new_category_empty_content',
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

    public function testCompleteFamilyCreationWithCodeProvided()
    {
        $client = static::createClient();
        $data =
<<<JSON
    {
        "code": "complete_family_creation_code",
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

        $client->request('PATCH', 'api/rest/v1/families/complete_family_creation_code', [], [], [], $data);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('complete_family_creation_code');
        $familyStandard = [
            'code'                   => 'complete_family_creation_code',
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

    public function testCompleteFamilyCreationWithoutCodeProvided()
    {
        $client = static::createClient();
        $data =
<<<JSON
    {
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

        $client->request('PATCH', 'api/rest/v1/families/complete_family_creation', [], [], [], $data);

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

    public function testPartialUpdateWithAnEmptyContent()
    {
        $client = static::createClient();
        $data = '{}';

        $client->request('PATCH', 'api/rest/v1/families/familyA2', [], [], [], $data);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA2');
        $familyStandard = [
            'code'                   => 'familyA2',
            'attributes'             => ['a_metric', 'a_number_float', 'sku'],
            'attribute_as_label'     => 'sku',
            'attribute_requirements' => [
                'ecommerce' => ['a_metric', 'sku'],
                'tablet'    => ['a_number_float', 'sku'],
            ],
            'labels'                 => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.family');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($familyStandard, $normalizer->normalize($family));
    }

    public function testPartialUpdateWithCodeProvided()
    {
        $client = static::createClient();
        $data =
<<<JSON
    {
        "code": "familyA1",
        "attributes": ["an_image"],
        "attribute_as_label": "sku",
        "attribute_requirements": {
            "ecommerce": ["sku", "an_image"]
        },
        "labels": {
            "en_US": "Family A1 US",
            "fr_FR": "Family A1 FR"
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/families/familyA1', [], [], [], $data);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA1');
        $familyStandard = [
            'code'                   => 'familyA1',
            'attributes'             => ['an_image', 'sku'],
            'attribute_as_label'     => 'sku',
            'attribute_requirements' => [
                'ecommerce' => ['an_image', 'sku'],
                'tablet'    => ['sku'],
            ],
            'labels'                 => [
                'en_US' => 'Family A1 US',
                'fr_FR' => 'Family A1 FR',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.family');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($familyStandard, $normalizer->normalize($family));
    }

    public function testPartialUpdateWithoutCodeProvided()
    {
        $client = static::createClient();
        $data =
<<<JSON
    {
        "attributes": ["sku", "a_metric"],
        "attribute_as_label": "sku",
        "attribute_requirements": {
            "tablet": ["sku", "a_metric"]
        },
        "labels": {
            "en_US": "Family A2 US"
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/families/familyA2', [], [], [], $data);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA2');
        $familyStandard = [
            'code'                   => 'familyA2',
            'attributes'             => ['a_metric', 'sku'],
            'attribute_as_label'     => 'sku',
            'attribute_requirements' => [
                'ecommerce' => ['sku'],
                'tablet'    => ['a_metric', 'sku'],
            ],
            'labels'                 => [
                'en_US' => 'Family A2 US',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.family');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
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

        $client->request('PATCH', 'api/rest/v1/families/familyA', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenValidationFailed()
    {
        $client = static::createClient();
        $data =
<<<JSON
    {
        "code": "!invalid_character"
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'code',
                    'message' => 'This property cannot be changed.',
                ],
                [
                    'field'   => 'code',
                    'message' => 'Family code may contain only letters, numbers and underscores',
                ],
            ],
        ];

        $client->request('PATCH', 'api/rest/v1/families/familyA', [], [], [], $data);

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

        $client->request('PATCH', 'api/rest/v1/families/familyA', [], [], [], $data);

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

        $client->request('PATCH', 'api/rest/v1/families/familyA', [], [], [], $data);

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
