<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Controller;

use Akeneo\Platform\Job\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class IndexActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'akeneo_job_index_action';

    public function setUp(): void
    {
        parent::setUp();

        $this->fixturesLoader->loadFixtures();
        $this->logAs('peter');
    }

    public function test_it_returns_job_total_count(): void
    {
        $this->webClientHelper->callApiRoute($this->client, self::ROUTE, [], 'POST');

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);
        Assert::assertSame(json_decode($response->getContent(), true)['total_count'], 1);
    }
}
