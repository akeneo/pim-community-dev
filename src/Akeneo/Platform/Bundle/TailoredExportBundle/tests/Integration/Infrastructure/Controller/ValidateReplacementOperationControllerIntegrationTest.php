<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Controller;

use Akeneo\Platform\TailoredExport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class ValidateReplacementOperationControllerIntegrationTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_export_validate_replacement_operation_action';
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    public function test_it_returns_ok_if_replacement_operation_is_valid(): void
    {
        $response = $this->callValidateReplacementOperationRoute([
            'black' => str_repeat('b', 254)
        ]);
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);
    }

    public function test_it_returns_errors_if_replacement_operation_is_not_valid(): void
    {
        $response = $this->callValidateReplacementOperationRoute([
            'black' => str_repeat('b', 256)
        ]);
        Assert::assertSame($response->getStatusCode(), Response::HTTP_UNPROCESSABLE_ENTITY);
        $responseContent = json_decode($response->getContent(), true);
        Assert::assertSame('akeneo.tailored_export.validation.max_length_reached', $responseContent[0]['messageTemplate']);
        Assert::assertSame('[mapping][black]', $responseContent[0]['propertyPath']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function callValidateReplacementOperationRoute(array $mapping): Response
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'POST',
            [],
            json_encode([
                'type' => 'replacement',
                'mapping' => $mapping
            ])
        );

        return $this->client->getResponse();
    }
}
