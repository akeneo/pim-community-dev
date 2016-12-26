<?php

namespace tests\integration\Pim\Bundle\ApiBundle\Controller\Rest\Category;

use Symfony\Component\HttpFoundation\Response;
use Test\Integration\TestCase;

class CreateCategoryIntegration extends TestCase
{
    public function testResponseWhenContentIsNotValid()
    {
        $client = static::createClient();

        $data = '';

        $client->request('POST', 'api/rest/v1/categories/', [], [], [], $data);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), 'Category has not been created');
        $this->assertSame('JSON is not valid.', $content['message']);
    }

    public function testHttpHeadersInResponseWhenACategoryIsCreated()
    {
        $client = static::createClient();

        $data =
<<<JSON
    {
        "code": "new_category"
    }
JSON;

        $client->request('POST', 'api/rest/v1/categories/', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode(), 'Category has been created');
        $this->assertArrayHasKey('location', $response->headers->all(), 'Location is in headers');
        $this->assertSame('http://localhost/api/rest/v1/categories/new_category', $response->headers->get('location'), 'New Category URI');
    }

    public function testFormatStandardWhenACategoryIsCreatedButUncompleted()
    {
        $client = static::createClient();

        $data =
<<<JSON
    {
        "code": "new_category"
    }
JSON;

        $client->request('POST', 'api/rest/v1/categories/', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('new_category');
        $categoryStandard = [
            'code'   => 'new_category',
            'parent' => null,
            'labels' => []
        ];

        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $this->assertSame($categoryStandard, $normalizer->normalize($category), 'Standard format is respected');
    }

    public function testCompleteCategoryCreation()
    {
        $client = static::createClient();

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

        $client->request('POST', 'api/rest/v1/categories/', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('categoryC');
        $categoryStandard = [
            'code'   => 'categoryC',
            'parent' => 'master',
            'labels' => [
                'en_US' => 'Category C',
                'fr_FR' => 'Catégorie C'
            ]
        ];

        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $this->assertSame($categoryStandard, $normalizer->normalize($category), 'Standard format is respected');
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

        $client->request('POST', 'api/rest/v1/categories/', [], [], [], $data);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode(), 'Category has not been created');
        $this->assertSame('Validation failed.', $content['message']);
        $this->assertCount(1, $content['errors']);

        $errors = [['field' => 'code', 'message' => 'This value should not be blank.']];
        $this->assertSame($errors, $content['errors']);
    }

    public function testResponseWhenAPropertyIsNotExpected()
    {
        $client = static::createClient();

        $data =
<<<JSON
    {
        "code": "sales",
        "extra_property": ""
    }
JSON;

        $client->request('POST', 'api/rest/v1/categories/', [], [], [], $data);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), 'Category has not been created');
        $this->assertSame('Property "extra_property" does not exist. Check the standard format documentation.', $content['message']);
    }

    /**
     * @group test
     */
    public function testResponseWhenLabelsIsNull()
    {
        $client = static::createClient();

        $data =
<<<JSON
    {
        "labels": null
    }
JSON;

        $client->request('POST', 'api/rest/v1/categories/', [], [], [], $data);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode(), 'Category has not been created');
        $this->assertSame('Labels of category cannot be null.', $content['message']);
    }
}
