<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Controller;

use Akeneo\Platform\Job\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetJobTypesActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'akeneo_job_get_job_types_action';
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
        $this->fixturesLoader->loadFixtures();
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
        Assert::assertEqualsCanonicalizing(json_decode($response->getContent(), true), $expectedJobTypes);
    }
}
