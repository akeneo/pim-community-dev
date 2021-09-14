<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternalApi;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetCategoriesByAccessLevelActionEndToEnd extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_returns_a_response(): void
    {
        $this->authenticateAsAdmin();
        $this->client->request(
            'GET',
            '/rest/permissions/category/edit?offset=10&limit=10',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $result = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
