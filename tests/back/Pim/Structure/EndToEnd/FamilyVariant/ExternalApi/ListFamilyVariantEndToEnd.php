<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\EndToEnd\FamilyVariant\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Symfony\Component\HttpFoundation\Response;

class ListFamilyVariantEndToEnd extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamily([
            'code'        => 'familyB',
            'attributes'  => ['sku', 'a_simple_select', 'a_yes_no'],
            'attribute_requirements' => [
                'tablet' => ['sku', 'a_simple_select', 'a_yes_no']
            ]
        ]);

        $this->createFamilyVariant([
            'code'        => 'variantFamilyB',
            'family'      => 'familyB',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['a_simple_select'],
                    'attributes' => ['a_simple_select', 'a_yes_no', 'sku'],
                ],
            ]
        ]);
    }

    /**
     * @group critical
     * TODO: to test with pagination
     */
    public function testListFamilyVariants()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/families/familyA/variants');

        $expected = <<<JSON
{
    "_links" : {
        "first" : {
            "href" : "http://localhost/api/rest/v1/families/familyA/variants?page=1&limit=10&with_count=false"
        },
        "self" : {
            "href" : "http://localhost/api/rest/v1/families/familyA/variants?page=1&limit=10&with_count=false"
        }
    },
    "current_page" : 1,
    "_embedded" : {
        "items" : [
            {
                "code" : "familyVariantA1",
                "labels" : {

                },
                "variant_attribute_sets" : [
                    {
                        "level" : 1,
                        "attributes" : [
                            "a_simple_select",
                            "a_text"
                        ],
                        "axes" : [
                            "a_simple_select"
                        ]
                    },
                    {
                        "level" : 2,
                        "attributes" : [
                            "sku",
                            "a_text_area",
                            "a_yes_no"
                        ],
                        "axes" : [
                            "a_yes_no"
                        ]
                    }
                ],
                "_links" : {
                    "self" : {
                        "href" : "http://localhost/api/rest/v1/families/familyA/variants/familyVariantA1"
                    }
                }
            },
            {
                "code" : "familyVariantA2",
                "labels" : {

                },
                "variant_attribute_sets" : [
                    {
                        "level" : 1,
                        "attributes" : [
                            "sku",
                            "a_simple_select",
                            "a_text",
                            "a_yes_no"
                        ],
                        "axes" : [
                            "a_simple_select",
                            "a_yes_no"
                        ]
                    }
                ],
                "_links" : {
                    "self" : {
                        "href" : "http://localhost/api/rest/v1/families/familyA/variants/familyVariantA2"
                    }
                }
            }
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testListFamilyVariantsWithCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/families/familyA/variants?limit=1&page=2&with_count=true');

        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/families/familyA/variants?page=2&limit=1&with_count=true"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/families/familyA/variants?page=1&limit=1&with_count=true"
		},
		"previous": {
			"href": "http://localhost/api/rest/v1/families/familyA/variants?page=1&limit=1&with_count=true"
		},
		"next": {
			"href": "http://localhost/api/rest/v1/families/familyA/variants?page=3&limit=1&with_count=true"
		}
	},
	"current_page": 2,
	"items_count": 2,
	"_embedded": {
		"items": [
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/families/familyA/variants/familyVariantA2"
                    }
                },
                "code": "familyVariantA2",
                "labels": {},
                "variant_attribute_sets": [
                    {
                        "level": 1,
                        "axes": ["a_simple_select", "a_yes_no"],
                        "attributes": ["sku", "a_simple_select", "a_text", "a_yes_no"]
                    }
                ]
            }
		]
	}
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testOutOfRangeListFamilyVariants()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/families/familyA/variants?limit=10&page=3');

        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/families/familyA/variants?page=3&limit=10&with_count=false"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/families/familyA/variants?page=1&limit=10&with_count=false"
		},
		"previous": {
			"href": "http://localhost/api/rest/v1/families/familyA/variants?page=2&limit=10&with_count=false"
		}
	},
	"current_page": 3,
	"_embedded": {
		"items": []
	}
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testNotFoundFamily()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/families/unknownFamily/variants');
        $expected = <<<JSON
{
    "code" : 404,
    "message" : "Family \"unknownFamily\" does not exist."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @param array $data
     *
     * @return FamilyInterface
     */
    protected function createFamily(array $data = []): FamilyInterface
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $constraintList = $this->get('validator')->validate($family);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    /**
     * @param array $data
     *
     * @return FamilyVariantInterface
     */
    protected function createFamilyVariant(array $data = []): FamilyVariantInterface
    {
        $family = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family, $data);
        $constraintList = $this->get('validator')->validate($family);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.family_variant')->save($family);

        return $family;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
