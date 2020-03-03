<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\EndToEnd\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetMeasurementFamiliesEndToEnd extends ApiTestCase
{
    /**
     * @test
     */
    public function it_returns_the_list_of_measurement_families()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/measurement-families');

        $expected = $this->getExpectedJSON('measurement-families-first-page');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @test
     */
    public function it_returns_no_items_when_the_requested_page_is_out_of_range()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/measurement-families?page=300');

        $expected = <<<JSON
{
  "_links": {
    "self": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measurement-families?page=300&limit=10&with_count=false"
    },
    "first": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measurement-families?page=1&limit=10&with_count=false"
    },
    "previous": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measurement-families?page=299&limit=10&with_count=false"
    }
  },
  "current_page": 300,
  "_embedded": {
    "items": []
  }
}
JSON;
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @test
     */
    public function it_returns_the_requested_page_with_the_requested_limit()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/measurement-families?page=2&limit=3');

        $expected = $this->getExpectedJSON('measurement-families-page-limit');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @test
     */
    public function it_returns_the_total_count_when_requested()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/measurement-families?with_count=true&limit=1');

        $expected = $this->getExpectedJSON('measurement-families-count-limit');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @test
     */
    public function it_422_when_the_pagination_is_not_supported()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/measurement-families?pagination_type=search_after');

        $expected = '{"code":422,"message":"Pagination type is not supported."}';
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertEquals($response->getContent(), $expected);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getExpectedJSON(string $expected)
    {
        return file_get_contents(sprintf('%s/Responses/%s.json', __DIR__, $expected));
    }
}
