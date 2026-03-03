<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Controller;

use Akeneo\Platform\Job\Test\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetJobTypeActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'akeneo_job_get_job_type_action';

    public function setUp(): void
    {
        parent::setUp();

        $this->fixturesLoader->loadFixtures();
        $this->logAs('julia');
    }

    public function test_it_returns_job_types(): void
    {
        $this->webClientHelper->callApiRoute($this->client, self::ROUTE);

        $expectedJobTypes = [
            'import',
            'export',
        ];

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);
        Assert::assertEqualsCanonicalizing($expectedJobTypes, json_decode($response->getContent(), true));
    }

    public function test_it_returns_a_forbidden_access_when_user_cannot_access_to_process_tracker(): void
    {
        $this->logAs('betty');

        $this->webClientHelper->callApiRoute($this->client, self::ROUTE);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_FORBIDDEN);
    }
}
