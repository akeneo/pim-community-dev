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
use Symfony\Component\HttpFoundation\Response;

final class GetStorageConnectionCheckActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_job_automation_get_storage_connection_check';
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

    public function test_it_returns_a_400_if_content_is_wrong()
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'POST',
            [],
            '{"type": "sftp", "file_path": "import_%job_label%_%datetime%.xlsx", "host": "127.0.0.1", "port": 22,}'
        );
        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = \json_decode($response->getContent(), true);
        $this->assertEqualsCanonicalizing(null, $response);
    }

    public function test_it_returns_a_healthy_false_if_connection_failed()
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'POST',
            [],
            '{"type": "sftp", "file_path": "import_%job_label%_%datetime%.xlsx", "host": "127.0.0.1", "port": 22, "login_type": "password", "username": "foo", "password": "bar"}'
        );
        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = \json_decode($response->getContent(), true);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
