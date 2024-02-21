<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\FamilyVariant\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateFamilyVariantEndToEnd extends ApiTestCase
{
    public function testStandardFormatWhenAFamilyVariantIsCreated()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "code": "new_family_variant",
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

        $client->request('POST', 'api/rest/v1/families/familyA/variants', [], [], [], $data);

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
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/families/familyA/variants/new_family_variant', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
        $this->assertSame($familyVariantStandard, $normalizer->normalize($familyVariant));
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

        $client->request('POST', 'api/rest/v1/families/familyA/variants', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenContentIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{';
        $expectedContent = <<<JSON
{"code":400,"message":"Invalid json message received"}
JSON;

        $client->request('POST', 'api/rest/v1/families/familyA/variants', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedContent, $response->getContent());
    }

    public function testResponseWhenValidationFailed()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "code": "new_family_variant",
    "variant_attribute_sets": [
        {
            "level": 2,
            "axes": ["a_yes_no"],
            "attributes": ["a_yes_no"]
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
            "message":"There is no variant attribute set for level \"1\""
        }
    ]
}
JSON;

        $client->request('POST', 'api/rest/v1/families/familyA/variants', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenAttributeSetIsNotAnArray()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "code": "new_family_variant",
    "variant_attribute_sets": ["yo"]
}
JSON;

        $expectedContent = <<<JSON
{
	"code": 422,
	"message": "Property \"variant_attribute_sets\" expects an array of objects as data. Check the expected format on the API documentation.",
	"_links": {
		"documentation": {
			"href": "http://api.akeneo.com/api-reference.html#post_families__family_code__variants"
		}
	}
}
JSON;

        $client->request('POST', 'api/rest/v1/families/familyA/variants', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenFamilyIsProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "code": "new_family_variant",
    "family": "familyA",
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
    ]
}
JSON;

        $expectedContent = <<<JSON
{
    "code": 422,
    "message": "Property \"family\" does not exist. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
            "href": "http://api.akeneo.com/api-reference.html#post_families__family_code__variants"
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/families/familyA/variants', [], [], [], $data);
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
