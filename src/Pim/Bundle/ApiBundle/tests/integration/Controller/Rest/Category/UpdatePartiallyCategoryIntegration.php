<?php

namespace tests\integration\Pim\Bundle\ApiBundle\Controller\Rest\Category;

use Symfony\Component\HttpFoundation\Response;
use Test\Integration\TestCase;

class UpdatePartiallyCategoryIntegration extends TestCase
{
    public function testResponseWhenContentIsNotValid()
    {
        $client = static::createClient();

        $data = '';

        $client->request('PATCH', 'api/rest/v1/categories/master', [], [], [], $data);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), 'Category has not been created');
        $this->assertSame('JSON is not valid.', $content['message']);
    }

    public function testResponseWhenContentIsValidButEmpty()
    {
        $client = static::createClient();

        $data =
<<<JSON
    {}
JSON;

        $client->request('PATCH', 'api/rest/v1/categories/master', [], [], [], $data);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), 'Category has not been created');
        $this->assertSame('Nothing to update.', $content['message']);
    }

    public function testHttpHeadersInResponseWhenACategoryIsPartiallyUpdated()
    {
        $client = static::createClient();

        $data =
<<<JSON
    {
        "parent": null,
        "labels": {
            "en_US": "Category B"
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/categories/categoryB', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), 'Category has been updated');
        $this->assertArrayHasKey('location', $response->headers->all(), 'Location is in headers');
        $this->assertSame('http://localhost/api/rest/v1/categories/categoryB', $response->headers->get('location'), 'Category URI');
    }

    public function testFormatStandardWhenACategoryIsPartiallyUpdated()
    {
        $client = static::createClient();

        $data =
<<<JSON
    {
        "labels": {
            "en_US": "Category B"
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/categories/categoryB', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('categoryB');
        $categoryStandard = [
            'code'   => 'categoryB',
            'parent' => 'master',
            'labels' => [
                'en_US' => 'Category B'
            ]
        ];

        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $this->assertSame($categoryStandard, $normalizer->normalize($category), 'Standard format is respected');
    }

    public function testFormatStandardWithNotAllLabelsUpdatedOnPartialUpdate()
    {
        $client = static::createClient();

        $data =
<<<JSON
    {
        "labels": {
            "en_US": "CategoryA"
        }
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/categories/categoryA', [], [], [], $data);

        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('categoryA');
        $categoryStandard = [
            'code'   => 'categoryA',
            'parent' => 'master',
            'labels' => [
                'en_US' => 'CategoryA',
                'fr_FR' => 'Catégorie A'
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

        $client->request('PATCH', 'api/rest/v1/categories/master', [], [], [], $data);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode(), 'Category has not been created');
        $this->assertSame('Validation failed.', $content['message']);
        $this->assertCount(2, $content['errors']);
        $errors = [
            ['field' => 'code', 'message' => 'This property cannot be changed.'],
            ['field' => 'code', 'message' => 'This value should not be blank.'],
        ];
        $this->assertSame($errors, $content['errors']);
    }

    public function testResponseWhenAPropertyIsNotExpected()
    {
        $client = static::createClient();

        $data =
<<<JSON
    {
        "code": "categoryA",
        "extra": ""
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/categories/master', [], [], [], $data);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), 'Category has not been created');
        $this->assertSame('Property "extra" does not exist. Check the standard format documentation.', $content['message']);
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
