<?php

namespace Pim\Bundle\ApiBundle\tests\integration\EventSubscriber;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class SessionSubscriberIntegration extends ApiTestCase
{
    public function testThereIsNoSessionCookieOnTheApi(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories');

        $response = $client->getResponse();
        $sessionCookie = $client->getCookieJar()->get('MOCKSESSID');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNull($sessionCookie);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
