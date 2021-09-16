<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternalApi;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetAttributeGroupsForPermissionsActionEndToEnd extends WebTestCase
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
            '/rest/permissions/attribute-group?offset=0&limit=1',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $result = json_decode($this->client->getResponse()->getContent(), true);
        Assert::assertEquals([
            'next' => [
                'url' => 'http://localhost/rest/permissions/attribute-group?ui_locale=en_US&search=&offset=1&limit=1',
                'params' => [
                    'ui_locale' => 'en_US',
                    'search' => '',
                    'offset' => 1,
                    'limit' => 1,
                ],
            ],
            'results' => [
                [
                    'code' => 'other',
                    'label' => 'Other',
                ],
            ],
        ], $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
