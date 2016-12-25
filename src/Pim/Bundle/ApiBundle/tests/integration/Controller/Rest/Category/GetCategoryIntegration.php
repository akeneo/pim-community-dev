<?php

namespace tests\integration\Pim\Bundle\ApiBundle\Controller\Rest\Category;

use Symfony\Component\HttpFoundation\Response;
use Test\Integration\TestCase;

class GetCategoryIntegration extends TestCase
{
    public function testGetACategory()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/categories/master');

        $standardCategory = [
            'code'   => 'master',
            'parent' => null,
            'labels' => []
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($standardCategory, json_decode($response->getContent(), true));
    }

    public function testNotFoundACategory()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/categories/not_found');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Category "not_found" does not exist.', $content['message']);
    }
}
