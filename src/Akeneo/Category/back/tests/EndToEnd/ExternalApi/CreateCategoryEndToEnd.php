<?php

namespace Akeneo\Test\Category\EndToEnd\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedCategoryCleaner;
use Symfony\Component\HttpFoundation\Response;

class CreateCategoryEndToEnd extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenACategoryIsCreated(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code": "new_category_headers"
    }
JSON;

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/categories/new_category_headers', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
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

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('new_category_incompleted');
        $categoryStandard = [
            'code' => 'new_category_incompleted',
            'parent' => null,
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $categoryNormalized = $normalizer->normalize($category);

        NormalizedCategoryCleaner::clean($categoryStandard);
        NormalizedCategoryCleaner::clean($categoryNormalized);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertSame($categoryStandard, $categoryNormalized);
    }

    /**
     * @group critical
     */
    public function testCompleteCategoryCreation(): void
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
        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

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
        $categoryNormalized = $normalizer->normalize($category);

        NormalizedCategoryCleaner::clean($categoryStandard);
        NormalizedCategoryCleaner::clean($categoryNormalized);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertSame($categoryStandard, $categoryNormalized);
    }

    public function testCategoryCreationWithEmptyLabels(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code": "empty_label_category",
        "parent": "master",
        "labels": {
            "en_US": "US label",
            "fr_FR": null,
            "de_DE": ""
        }
    }
JSON;
        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('empty_label_category');
        $categoryStandard = [
            'code' => 'empty_label_category',
            'parent' => 'master',
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => [
                'en_US' => 'US label',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $categoryNormalized = $normalizer->normalize($category);

        NormalizedCategoryCleaner::clean($categoryStandard);
        NormalizedCategoryCleaner::clean($categoryNormalized);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertSame($categoryStandard, $categoryNormalized);
    }

    public function testResponseWhenContentIsEmpty(): void
    {
        $client = $this->createAuthenticatedClient();

        $data = '';

        $expectedContent = [
            'code' => 400,
            'message' => 'Invalid json message received',
        ];

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);
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

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenCategoryCodeAlreadyExists(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code": "categoryA"
    }
JSON;

        $expectedContent = [
            'code' => 422,
            'message' => 'Validation failed.',
            'errors' => [
                [
                    'property' => 'code',
                    'message' => 'This value is already used.',
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenValidationFailed(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code": ""
    }
JSON;

        $expectedContent = [
            'code' => 422,
            'message' => 'Validation failed.',
            'errors' => [
                [
                    'property' => 'code',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

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
        "code": "sales",
        "extra_property": ""
    }
JSON;

        $expectedContent = [
            'code' => 422,
            'message' => 'Property "extra_property" does not exist. Check the expected format on the API documentation.',
            '_links' => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_categories',
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

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
                    'href' => 'http://api.akeneo.com/api-reference.html#post_categories',
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenLocaleCodeInLabelsIsEmpty(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code": "test_empty_locale",
        "labels": {
            "": "label"
         }
    }
JSON;

        $expectedContent = [
            'code' => 422,
            'message' => 'Validation failed.',
            'errors' => [
                [
                    'property' => 'labels',
                    'message' => 'The locale "" does not exist.',
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenLocaleCodeDoesNotExist(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code": "test_unknown_locale",
        "labels": {
            "foo": "label"
         }
    }
JSON;

        $expectedContent = [
            'code' => 422,
            'message' => 'Validation failed.',
            'errors' => [
                [
                    'property' => 'labels',
                    'message' => 'The locale "foo" does not exist.',
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
