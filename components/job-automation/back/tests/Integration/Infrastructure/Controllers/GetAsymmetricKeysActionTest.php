<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Test\Integration\Infrastructure\Controllers;

use Akeneo\Platform\JobAutomation\Application\GenerateAsymmetricKeys\GenerateAsymmetricKeysHandler;
use Akeneo\Platform\JobAutomation\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetAsymmetricKeysActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_job_automation_get_public_key';
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('peter', $this->client);
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');

        /** @var GenerateAsymmetricKeysHandler $generateAsymmetricKeysHandler */
        $generateAsymmetricKeysHandler = $this->get('akeneo.job_automation.handler.generate_asymmetric_keys');
        $generateAsymmetricKeysHandler->handle();
    }

    public function test_it_returns_the_asymmetric_key()
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'GET',
            [],
        );
        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
