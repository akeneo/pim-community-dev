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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Controller\SampleData;

use Akeneo\Platform\TailoredImport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Symfony\Component\HttpFoundation\Response;

class GeneratePreviewDataActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_import_generate_preview_data_action';
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    public function test_it_returns_a_generated_preview_data(): void
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'POST',
            [
                'sample_data' => ['<b>Produit 1</b>', 'Produit 4', 'Produit 3'],
                'operations' => [
                    [
                        'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                        'modes' => ['remove'],
                        'type' => 'clean_html',
                    ],
                ],
                'target' => [
                    'code' => 'name',
                    'type' => 'attribute',
                    'attribute_type' => 'pim_catalog_text',
                    'locale' => null,
                    'channel' => null,
                    'source_configuration' => [],
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ],
            ],
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $decodedResponse = \json_decode($response->getContent(), true);
        $expectedResponse = ['preview_data' => ['ad4e2d5c-2830-4ba8-bf83-07f9935063d6' => [
            [
                'type' => 'string',
                'value' => 'Produit 1',
            ],
            [
                'type' => 'string',
                'value' => 'Produit 4',
            ],
            [
                'type' => 'string',
                'value' => 'Produit 3',
            ],
        ]]];

        $this->assertSame($expectedResponse, $decodedResponse);
    }

    public function test_it_returns_a_validation_error_when_operation_does_not_exist(): void
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'POST',
            [
                'sample_data' => ['<b>Produit 1</b>', 'Produit 4', 'Produit 3'],
                'operations' => [
                    ['type' => 'unknown_operation'],
                ],
                'target' => [
                    'code' => 'name',
                    'type' => 'attribute',
                    'attribute_type' => 'pim_catalog_text',
                    'locale' => null,
                    'channel' => null,
                    'source_configuration' => [],
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ],
            ],
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $decodedResponse = \json_decode($response->getContent(), true);
        $this->assertContains(
            'akeneo.tailored_import.validation.operations.operation_type_does_not_exist',
            array_column($decodedResponse, 'messageTemplate'),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
