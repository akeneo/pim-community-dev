<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\EndToEnd\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetMeasurementFamiliesActionEndToEnd extends ApiTestCase
{
    /**
     * @test
     */
    public function it_returns_the_list_of_measurement_families()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/measurement-families');

        $expected = $this->getExpectedJSON('measurement-families.json');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
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
        return file_get_contents(sprintf('%s/Responses/%s', __DIR__, $expected));
    }
}
