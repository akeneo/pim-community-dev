<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this target code.
 */

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Controller;

use Akeneo\Platform\TailoredImport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetGroupedTargetsControllerIntegrationTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_import_get_grouped_targets_action';
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    public function test_it_returns_available_grouped_targets(): void
    {
        $response = $this->assertCallSuccess(4, ['attribute' => 2, 'system' => 2], 'name');
        $targetGroups = \json_decode($response->getContent(), true);

        $expectedTargetGroups = [
            'results' => [
                [
                    'code' => 'marketing',
                    'label' => 'Marketing',
                    'children' => [
                        [
                            'code' => 'variation_name',
                            'label' => 'Variation Name',
                            'type' => 'attribute',
                        ]
                    ],
                ]
            ],
            'offset' => [
                'system' => 2,
                'attribute' => 3,
            ],
        ];

        $this->assertSame($expectedTargetGroups, $targetGroups);
    }

    private function assertCallSuccess(int $limit, array $offset, string $search = null): Response
    {
        $options = [
            'limit' => $limit,
            'offset' => $offset,
        ];

        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'GET',
            ['options' => $options, 'search' => $search]
        );

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
