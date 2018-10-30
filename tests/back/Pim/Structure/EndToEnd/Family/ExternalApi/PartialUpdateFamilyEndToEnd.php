<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Family\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateFamilyEndToEnd extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenAFamilyIsUpdated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "attribute_as_label": "sku"
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
        $client = $this->createAuthenticatedClient();

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

    public function testFormatStandardWhenAFamilyIsCreatedButIncompleted()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "new_family_incompleted"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/families/new_family_incompleted', [], [], [], $data);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('new_family_incompleted');
        $familyStandard = [
            'code'                   => 'new_family_incompleted',
            'attributes'             => ['sku'],
            'attribute_as_label'     => 'sku',
            'attribute_as_image'     => null,
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

    public function testFormatStandardWhenAFamilyIsCreatedWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', 'api/rest/v1/families/new_category_empty_content', [], [], [], $data);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('new_category_empty_content');
        $familyStandard = [
            'code'                   => 'new_category_empty_content',
            'attributes'             => ['sku'],
            'attribute_as_label'     => 'sku',
            'attribute_as_image'     => null,
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

    public function testCompleteFamilyCreationWithCodeProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "complete_family_creation_code",
        "attributes": ["an_image", "a_metric", "a_price", "an_image"],
        "attribute_as_label": "sku",
        "attribute_as_image": "an_image",
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
            'attribute_as_image'     => 'an_image',
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

    public function testCompleteFamilyCreationWithoutCodeProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "attributes": ["an_image", "a_metric", "a_price"],
        "attribute_as_label": "sku",
        "attribute_as_image": "an_image",
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
            'attribute_as_image'     => 'an_image',
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

    public function testPartialUpdateWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', 'api/rest/v1/families/familyA2', [], [], [], $data);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA2');
        $familyStandard = [
            'code'                   => 'familyA2',
            'attributes'             => ['a_metric', 'a_number_float', 'sku'],
            'attribute_as_label'     => 'sku',
            'attribute_as_image'     => null,
            'attribute_requirements' => [
                'ecommerce'       => ['a_metric', 'sku'],
                'ecommerce_china' => ['sku'],
                'tablet'          => ['a_number_float', 'sku'],
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
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "familyA1",
        "attributes": ["sku", "a_date", "a_file", "a_localizable_image", "an_image"],
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
            'attributes'             => ['a_date', 'a_file', 'a_localizable_image', 'an_image', 'sku'],
            'attribute_as_label'     => 'sku',
            'attribute_as_image'     => null,
            'attribute_requirements' => [
                'ecommerce'       => ['an_image', 'sku'],
                'ecommerce_china' => ['sku'],
                'tablet'          => ['a_file', 'a_localizable_image', 'sku'],
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
        $client = $this->createAuthenticatedClient();

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
            'attribute_as_image'     => null,
            'attribute_requirements' => [
                'ecommerce'       => ['a_metric', 'sku'],
                'ecommerce_china' => ['sku'],
                'tablet'          => ['a_metric', 'sku'],
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

    public function testPartialUpdateWithEmptyLabels()
    {
        $initLabels = [
            'labels' => [
                'en_US' => 'Family A2 US',
                'fr_FR' => 'Family A2 FR',
                'de_DE' => 'Family A2 DE',
            ],
        ];

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA2');
        $this->get('pim_catalog.updater.family')->update($family, $initLabels);
        $this->get('pim_catalog.saver.family')->save($family);

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

        $client->request('PATCH', 'api/rest/v1/families/familyA2', [], [], [], $data);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA2');
        $familyStandard = [
            'code'                   => 'familyA2',
            'attributes'             => ['a_metric', 'a_number_float', 'sku'],
            'attribute_as_label'     => 'sku',
            'attribute_as_image'     => null,
            'attribute_requirements' => [
                'ecommerce'       => ['a_metric', 'sku'],
                'ecommerce_china' => ['sku'],
                'tablet'          => ['a_number_float', 'sku'],
            ],
            'labels'                 => [
                'de_DE' => 'Family A2 DE',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.family');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($familyStandard, $normalizer->normalize($family));
    }

    public function testPropertiesDeletionWithoutCodeProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "attributes": [ ],
        "attribute_as_label": "sku",
        "attribute_as_image": null,
        "attribute_requirements": {
            "ecommerce": [ ],
            "tablet": [ ]
        },
        "labels": { }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/families/familyA1', [], [], [], $data);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA1');
        $familyStandard = [
            'code'                   => 'familyA1',
            'attributes'             => [ 'sku' ],
            'attribute_as_label'     => 'sku',
            'attribute_as_image'     => null,
            'attribute_requirements' => [
                'ecommerce'          => [ 'sku' ],
                'ecommerce_china'    => [ 'sku' ],
                'tablet'             => [ 'sku' ],
            ],
            'labels'                 => [
                'en_US' => 'A family A1'
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.family');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
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

        $client->request('PATCH', 'api/rest/v1/families/familyA', [], [], [], $data);
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

        $client->request('PATCH', 'api/rest/v1/families/familyA', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenValidationFailed()
    {
        $client = $this->createAuthenticatedClient();
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
                    'property' => 'code',
                    'message'  => 'This property cannot be changed.',
                ],
                [
                    'property' => 'code',
                    'message'  => 'Family code may contain only letters, numbers and underscores',
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
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "extra_property": ""
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "extra_property" does not exist. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#patch_families__code_'
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
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "labels": null
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "labels" expects an array as data, "NULL" given. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#patch_families__code_'
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
        return $this->catalog->useTechnicalCatalog();
    }
}
