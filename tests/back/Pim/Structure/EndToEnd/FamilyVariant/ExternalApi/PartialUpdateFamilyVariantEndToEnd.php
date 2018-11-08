<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\EndToEnd\FamilyVariant\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateFamilyVariantEndToEnd extends ApiTestCase
{
    public function testFormatStandardWhenAFamilyVariantIsCreated()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "variant_attribute_sets": [
        {
            "level": 1,
            "axes": ["a_ref_data_simple_select"],
            "attributes": ["a_ref_data_simple_select"]
        },
        {
            "level": 2,
            "axes": ["a_yes_no"],
            "attributes": ["a_yes_no"]
        }
    ],
    "labels": {
        "en_US": "English label"
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/families/familyA/variants/new_family_variant', [], [], [], $data);

        $familyVariant = $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier('new_family_variant');
        $familyVariantStandard = [
            'code'                   => 'new_family_variant',
            'labels'                 => [
                'en_US' => 'English label',
            ],
            'family'                 => 'familyA',
            'variant_attribute_sets' => [
                [
                    'level'      => 1,
                    'axes'       => ['a_ref_data_simple_select'],
                    'attributes' => ['a_ref_data_simple_select'],
                ],
                [
                    'level'      => 2,
                    'axes'       => ['a_yes_no'],
                    'attributes' => ['a_yes_no', 'sku'],
                ],
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.family_variant');

        $response = $client->getResponse();
        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertArrayHasKey('location', $response->headers->all());
        self::assertSame('http://localhost/api/rest/v1/families/familyA/variants/new_family_variant', $response->headers->get('location'));
        self::assertSame('', $response->getContent());
        self::assertSame($familyVariantStandard, $normalizer->normalize($familyVariant));
    }

    public function testStandardFormatWhenAFamilyVariantIsUpdated()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "variant_attribute_sets": [
        {
            "level": 1,
            "axes": ["a_simple_select"],
            "attributes": ["a_metric"]
        },
        {
            "level": 2,
            "axes": ["a_yes_no"],
            "attributes": ["sku"]
        }
    ]
}
JSON;

        $client->request('PATCH', 'api/rest/v1/families/familyA/variants/familyVariantA1', [], [], [], $data);

        $familyVariant = $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier('familyVariantA1');
        $familyVariantStandard = [
            'code'                   => 'familyVariantA1',
            'labels'                 => [],
            'family'                 => 'familyA',
            'variant_attribute_sets' => [
                [
                    'level'      => 1,
                    'axes'       => ['a_simple_select'],
                    'attributes' => ['a_metric', 'a_simple_select'],
                ],
                [
                    'level'      => 2,
                    'axes'       => ['a_yes_no'],
                    'attributes' => ['sku', 'a_yes_no'],
                ],
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.family_variant');

        $response = $client->getResponse();
        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertArrayHasKey('location', $response->headers->all());
        self::assertSame('http://localhost/api/rest/v1/families/familyA/variants/familyVariantA1', $response->headers->get('location'));
        self::assertSame('', $response->getContent());
        self::assertSame($familyVariantStandard, $normalizer->normalize($familyVariant));
    }

    public function testResponseWhenContentIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data = '';
        $expectedContent = <<<JSON
{
    "code": 400,
    "message": "Invalid json message received"
}
JSON;

        $client->request('PATCH', 'api/rest/v1/families/familyA/variants/familyVariantA1', [], [], [], $data);
        $response = $client->getResponse();
        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenContentIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{';
        $expectedContent = <<<JSON
{"code":400,"message":"Invalid json message received"}
JSON;

        $client->request('PATCH', 'api/rest/v1/families/familyA/variants/familyVariantA1', [], [], [], $data);
        $response = $client->getResponse();
        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame($expectedContent, $response->getContent());
    }

    public function testResponseWhenFamilyIsProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "family": "familyA"
}
JSON;

        $expectedContent = <<<JSON
{
    "code": 422,
    "message": "Property \"family\" does not exist. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
            "href": "http://api.akeneo.com/api-reference.html#patch_families__family_code__variants__code__"
        }
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/families/familyA/variants/familyVariantA1', [], [], [], $data);
        $response = $client->getResponse();
        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenIncorrectStandardFormatProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "variant_attribute_sets": "foo"
}
JSON;

        $expectedContent = <<<JSON
{
	"code": 422,
	"message": "Property \"variant_attribute_sets\" expects an array of objects as data. Check the expected format on the API documentation.",
	"_links": {
		"documentation": {
			"href": "http://api.akeneo.com/api-reference.html#patch_families__family_code__variants__code__"
		}
	}
}
JSON;

        $client->request('PATCH', 'api/rest/v1/families/familyA/variants/familyVariantA1', [], [], [], $data);
        $response = $client->getResponse();
        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenDeletingOneLevel()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "code": "familyVariantA1",
    "variant_attribute_sets": [
        {
            "level": 1,
            "axes": ["a_simple_select"],
            "attributes": []
        }
    ]
}
JSON;

        $expectedContent = <<<JSON
{
    "code": 422,
    "message":"The number of variant attribute sets cannot be changed. Check the expected format on the API documentation.",
    "_links": {
		"documentation": {
			"href": "http://api.akeneo.com/api-reference.html#patch_families__family_code__variants__code__"
		}
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/families/familyA/variants/familyVariantA1', [], [], [], $data);
        $response = $client->getResponse();
        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenValidationFailed()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "code": "familyVariantA1",
    "variant_attribute_sets": [
        {
            "level": 1,
            "attributes": ["sku"]
        },
        {
            "level": 2
        }
    ]
}
JSON;

        $expectedContent = <<<JSON
{
    "code": 422,
    "message": "Validation failed.",
    "errors": [
        {
            "property":"variant_attribute_sets",
            "message":"Attributes must be unique, \"sku\" are used several times in variant attributes sets"
        }
    ]
}
JSON;

        $client->request('PATCH', 'api/rest/v1/families/familyA/variants/familyVariantA1', [], [], [], $data);
        $response = $client->getResponse();
        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }


    public function testResponseWhenAFamilyVariantIsCreatedWithInconsistentCodes()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "inconsistent_code1"
}
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'The code "inconsistent_code1" provided in the request body must match the code "inconsistent_code2" provided in the url.',
        ];

        $client->request('PATCH', 'api/rest/v1/families/familyA/variants/inconsistent_code2', [], [], [], $data);

        $response = $client->getResponse();
        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
