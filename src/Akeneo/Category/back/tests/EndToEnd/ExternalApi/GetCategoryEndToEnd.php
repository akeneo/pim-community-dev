<?php

namespace Akeneo\Test\Category\EndToEnd\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedCategoryCleaner;
use Symfony\Component\HttpFoundation\Response;

class GetCategoryEndToEnd extends ApiTestCase
{
    /**
     * @group critical
     */
    public function testGetACategory(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories/master');

        $expectedCategory = [
            'code' => 'master',
            'parent' => null,
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $expectedCategory);
    }

    public function testGetACompleteCategory(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories/categoryA');

        $expectedCategory = [
            'code' => 'categoryA',
            'parent' => 'master',
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => [
                'en_US' => 'Category A',
                'fr_FR' => 'Catégorie A',
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $expectedCategory);
    }

    public function testNotFoundACategory(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories/not_found');

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('Category "not_found" does not exist.', $content['message']);
    }

    public function testGetACategoryWithPosition(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories/categoryA?with_position=true');

        $expectedCategory = [
            'code' => 'categoryA',
            'parent' => 'master',
            'updated' => '2016-06-14T13:12:50+02:00',
            'position' => 1,
            'labels' => [
                'en_US' => 'Category A',
                'fr_FR' => 'Catégorie A',
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $expectedCategory);
    }

    public function testGetACategoryWithWrongCategoryCodeType(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories?search={"code":[{"operator":"IN","value":1234}]}');

        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertSame(
            [
                'code' => 400,
                'message' => 'In order to search on category codes you must send an array of category codes as value, integer given. This value should be of type iterable.',
            ],
            json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR),
        );
    }

    // @phpstan-ignore-next-line
    private function assertResponse(Response $response, array $expected): void
    {
        $result = json_decode($response->getContent(), true);

        NormalizedCategoryCleaner::clean($expected);
        NormalizedCategoryCleaner::clean($result);

        $this->assertEquals($expected, $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
