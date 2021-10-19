<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetTokenActionEndToEnd extends WebTestCase
{
    public function test_to_access_to_the_get_access_token_endpoint(): void
    {
        $this->client->request('GET', '/connect/apps/v1/token');
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
