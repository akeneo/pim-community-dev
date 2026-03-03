<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\tests\integration\EventSubscriber;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Psr\Log\Test\TestLogger;

class ApiRequestLogSubscriberIntegration extends ApiTestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testItLogTheApiRequest()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products', [], [], ['HTTP_ACCEPT' => 'application/json']);

        $logger = self::getContainer()->get('monolog.logger.api_requests');
        assert($logger instanceof TestLogger);

        $this->assertTrue(
            $logger->hasInfo([
                'message' => 'request',
                'context' => [
                    'method' => 'GET',
                    'path_info' => 'http://localhost/api/rest/v1/products',
                    'user' => 'admin',
                ],
            ]),
            'Expected request not found in the api logs.'
        );
    }
}
