<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Rest\Category;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Bundle\CatalogBundle\Version;
use Symfony\Component\HttpFoundation\Response;

class CreateCategoryIntegration extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenACategoryIsCreated()
    {
        $client = $this->createAuthentifiedClient();

        $data =
<<<JSON
    {
        "code": "new_category_headers"
    }
JSON;

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/categories/new_category_headers', $response->headers->get('location'));
        $this->assertSame([], json_decode($response->getContent(), true));
    }

    public function testFormatStandardWhenACategoryIsCreatedButUncompleted()
    {
        $client = $this->createAuthentifiedClient();

        $data =
<<<JSON
    {
        "code": "new_category_uncompleted"
    }
JSON;

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('new_category_uncompleted');
        $categoryStandard = [
            'code'   => 'new_category_uncompleted',
            'parent' => null,
            'labels' => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($categoryStandard, $normalizer->normalize($category));
    }

    public function testCompleteCategoryCreation()
    {
        $client = $this->createAuthentifiedClient();

        $data =
<<<JSON
    {
        "code": "categoryC",
        "parent": "master",
        "labels": {
            "en_US": "Category C",
            "fr_FR": "Catégorie C"
        }
    }
JSON;
        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('categoryC');
        $categoryStandard = [
            'code'   => 'categoryC',
            'parent' => 'master',
            'labels' => [
                'en_US' => 'Category C',
                'fr_FR' => 'Catégorie C',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($categoryStandard, $normalizer->normalize($category));
    }

    public function testResponseWhenContentIsNotValid()
    {
        $client = $this->createAuthentifiedClient();

        $data = '';

        $expectedContent = [
            'code'    => 400,
            'message' => 'JSON is not valid.',
        ];

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenCategoryCodeAlreadyExists()
    {
        $client = $this->createAuthentifiedClient();

        $data =
<<<JSON
    {
        "code": "categoryA"
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Category "categoryA" already exists.',
        ];

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }


    public function testResponseWhenValidationFailed()
    {
        $client = $this->createAuthentifiedClient();

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

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenAPropertyIsNotExpected()
    {
        $client = $this->createAuthentifiedClient();

        $data =
<<<JSON
    {
        "code": "sales",
        "extra_property": ""
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "extra_property" does not exist. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#category', $version),
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenLabelsIsNull()
    {
        $client = $this->createAuthentifiedClient();

        $data =
<<<JSON
    {
        "labels": null
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "labels" expects an array. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#category', $version),
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/categories', [], [], [], $data);

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
