<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration\Controller;

use Akeneo\Platform\Job\Test\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CheckInstanceCanBeResetActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'akeneo_installer_check_reset_instance';

    public function setUp(): void
    {
        parent::setUp();

        $this->logAs('julia');
    }

    public function test_it_returns_ok_when_no_job_is_queued_or_running(): void
    {
        $this->webClientHelper->callApiRoute($this->client, self::ROUTE);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);
    }

    public function test_it_returns_failure_when_a_job_is_queued(): void
    {
        //given a queued job execution queued

        $this->webClientHelper->callApiRoute($this->client, self::ROUTE);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_BAD_REQUEST);
    }

    public function test_it_returns_failure_when_a_job_is_running(): void
    {
        //given a queued job execution running

        $this->webClientHelper->callApiRoute($this->client, self::ROUTE);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_BAD_REQUEST);
    }
}
