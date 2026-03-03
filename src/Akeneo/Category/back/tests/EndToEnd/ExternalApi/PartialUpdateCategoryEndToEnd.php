<?php

namespace Akeneo\Test\Category\EndToEnd\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedCategoryCleaner;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateCategoryEndToEnd extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenACategoryIsUpdated(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "parent": null
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/categories/categoryA1', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/categories/categoryA1', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    public function testHttpHeadersInResponseWhenACategoryIsCreated(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "new_category_headers",
        "parent": null
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/categories/new_category_headers', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/categories/new_category_headers', $response->headers->get('location'));
        $this->assertSame(null, json_decode($response->getContent(), true));
    }

    public function testStandardFormatWhenACategoryIsCreatedButIncompleted(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "new_category_incompleted"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/categories/new_category_incompleted', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('new_category_incompleted');
        $categoryStandard = [
            'code' => 'new_category_incompleted',
            'parent' => null,
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $normalizedCategory = $normalizer->normalize($category);

        NormalizedCategoryCleaner::clean($normalizedCategory);
        NormalizedCategoryCleaner::clean($categoryStandard);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertSame($categoryStandard, $normalizedCategory);
    }

    public function testStandardFormatWhenACategoryIsCreatedWithAnEmptyContent(): void
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', 'api/rest/v1/categories/new_category_empty_content', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('new_category_empty_content');
        $categoryStandard = [
            'code' => 'new_category_empty_content',
            'parent' => null,
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $normalizedCategory = $normalizer->normalize($category);

        NormalizedCategoryCleaner::clean($categoryStandard);
        NormalizedCategoryCleaner::clean($normalizedCategory);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertSame($categoryStandard, $normalizedCategory);
    }

    /**
     * @group critical
     */
    public function testCompleteCategoryCreationWithCodeProvided(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "categoryD",
        "parent": "master",
        "labels": {
            "en_US": "Category D",
            "fr_FR": "Catégorie D"
        }
    }
JSON;
        $client->request('PATCH', 'api/rest/v1/categories/categoryD', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('categoryD');
        $categoryStandard = [
            'code' => 'categoryD',
            'parent' => 'master',
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => [
                'en_US' => 'Category D',
                'fr_FR' => 'Catégorie D',
            ],
        ];

        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $normalizedCategory = $normalizer->normalize($category);

        NormalizedCategoryCleaner::clean($categoryStandard);
        NormalizedCategoryCleaner::clean($normalizedCategory);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertSame($categoryStandard, $normalizedCategory);
    }

    public function testCompleteCategoryCreationWithoutCodeProvided(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "parent": "master",
        "labels": {
            "en_US": "Category D",
            "fr_FR": "Catégorie D"
        }
    }
JSON;
        $client->request('PATCH', 'api/rest/v1/categories/categoryD', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('categoryD');
        $categoryStandard = [
            'code' => 'categoryD',
            'parent' => 'master',
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => [
                'en_US' => 'Category D',
                'fr_FR' => 'Catégorie D',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $normalizedCategory = $normalizer->normalize($category);

        NormalizedCategoryCleaner::clean($categoryStandard);
        NormalizedCategoryCleaner::clean($normalizedCategory);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertSame($categoryStandard, $normalizedCategory);
    }

    public function testPartialUpdateWithAnEmptyContent(): void
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', 'api/rest/v1/categories/categoryA', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('categoryA');
        $categoryStandard = [
            'code' => 'categoryA',
            'parent' => 'master',
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => [
                'en_US' => 'Category A',
                'fr_FR' => 'Catégorie A',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $normalizedCategory = $normalizer->normalize($category);

        NormalizedCategoryCleaner::clean($normalizedCategory);
        NormalizedCategoryCleaner::clean($categoryStandard);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertSame($categoryStandard, $normalizedCategory);
    }

    public function testPartialUpdateWithCodeProvided(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "categoryA",
        "labels": {
            "en_US": "Category A updated"
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/categories/categoryA', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('categoryA');
        $categoryStandard = [
            'code' => 'categoryA',
            'parent' => 'master',
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => [
                'en_US' => 'Category A updated',
                'fr_FR' => 'Catégorie A',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $normalizedCategory = $normalizer->normalize($category);

        NormalizedCategoryCleaner::clean($normalizedCategory);
        NormalizedCategoryCleaner::clean($categoryStandard);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertSame($categoryStandard, $normalizedCategory);
    }

    public function testPartialUpdateWithoutCodeProvided(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "parent": "categoryA1",
        "labels": {
            "en_US": "Category A2 updated"
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/categories/categoryA2', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('categoryA2');
        $categoryStandard = [
            'code' => 'categoryA2',
            'parent' => 'categoryA1',
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => [
                'en_US' => 'Category A2 updated',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $normalizedCategory = $normalizer->normalize($category);

        NormalizedCategoryCleaner::clean($categoryStandard);
        NormalizedCategoryCleaner::clean($normalizedCategory);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertSame($categoryStandard, $normalizedCategory);
    }

    public function testPartialUpdateWithEmptyLabels(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "labels": {
            "en_US": null,
            "fr_FR":""
        }
    }
JSON;
        $client->request('PATCH', 'api/rest/v1/categories/categoryA', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('categoryA');
        $categoryStandard = [
            'code' => 'categoryA',
            'parent' => 'master',
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $normalizedCategory = $normalizer->normalize($category);

        NormalizedCategoryCleaner::clean($categoryStandard);
        NormalizedCategoryCleaner::clean($normalizedCategory);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertSame($categoryStandard, $normalizedCategory);
    }

    public function testResponseWhenContentIsEmpty(): void
    {
        $client = $this->createAuthenticatedClient();

        $data = '';

        $expectedContent = [
            'code' => 400,
            'message' => 'Invalid json message received',
        ];

        $client->request('PATCH', 'api/rest/v1/categories/categoryA', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenContentIsNotValid(): void
    {
        $client = $this->createAuthenticatedClient();

        $data = '{';

        $expectedContent = [
            'code' => 400,
            'message' => 'Invalid json message received',
        ];

        $client->request('PATCH', 'api/rest/v1/categories/categoryA', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenValidationFailed(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "new_code"
    }
JSON;

        $expectedContent = [
            'code' => 422,
            'message' => 'Validation failed.',
            'errors' => [
                [
                    'property' => 'code',
                    'message' => 'This property cannot be changed.',
                ],
            ],
        ];

        $client->request('PATCH', 'api/rest/v1/categories/categoryA', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenAPropertyIsNotExpected(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "extra_property": ""
    }
JSON;

        $expectedContent = [
            'code' => 422,
            'message' => 'Property "extra_property" does not exist. Check the expected format on the API documentation.',
            '_links' => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#patch_categories__code_',
                ],
            ],
        ];

        $client->request('PATCH', 'api/rest/v1/categories/categoryA', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenLabelsIsNull(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "labels": null
    }
JSON;

        $expectedContent = [
            'code' => 422,
            'message' => 'Property "labels" expects an array as data, "NULL" given. Check the expected format on the API documentation.',
            '_links' => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#patch_categories__code_',
                ],
            ],
        ];

        $client->request('PATCH', 'api/rest/v1/categories/categoryA', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenACategoryIsCreatedWithInconsistentCodes(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "inconsistent_code2"
    }
JSON;

        $expectedContent = [
            'code' => 422,
            'message' => 'The code "inconsistent_code2" provided in the request body must match the code "inconsistent_code1" provided in the url.',
        ];

        $client->request('PATCH', 'api/rest/v1/categories/inconsistent_code1', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenParentIsMovedInChildren(): void
    {
        $client = $this->createAuthenticatedClient();
        $categoryId = $this->get('pim_catalog.repository.category')->findOneByIdentifier('categoryA')->getId();

        $data = '{"parent": "categoryA1"}';
        $expectedContent = sprintf('{"code":422, "message": "Cannot set child as parent to node: %d"}', $categoryId);
        $client->request('PATCH', 'api/rest/v1/categories/categoryA', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenRootCategoryIsMovedToSubCategory(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('PATCH', 'api/rest/v1/categories/master', [], [], [], '{"parent": "categoryA"}');

        $expectedContent = [
            'code' => 422,
            'message' => 'You can\'t move a category tree linked to a user. Check the expected format on the API documentation.',
            '_links' => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#patch_categories__code_',
                ],
            ],
        ];

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
