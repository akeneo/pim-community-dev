<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c)  Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Controller;

use Akeneo\Platform\TailoredImport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Symfony\Component\HttpFoundation\Response;

class GetCategoryTreesActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_import_get_category_trees_action';
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    public function test_it_returns_all_category_trees()
    {
        $this->webClientHelper->callApiRoute($this->client, self::ROUTE);
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $response = \json_decode($response->getContent(), true);
        $expectedResponse = [
            [
                'id' => 2,
                'code' => 'master',
                'labels' => [
                    'en_US' => 'Master',
                    'fr_FR' => 'Master',
                    'de_DE' => 'Master',
                ],
            ],
            [
                'id' => 23,
                'code' => 'print',
                'labels' => [
                    'en_US' => 'Print',
                    'fr_FR' => 'Print',
                    'de_DE' => 'Print',
                ],
            ],
            [
                'id' => 27,
                'code' => 'suppliers',
                'labels' => [
                    'en_US' => 'Suppliers',
                    'fr_FR' => 'Suppliers',
                    'de_DE' => 'Suppliers',
                ],
            ]
        ];

        $this->assertEqualsCanonicalizing($expectedResponse, $response);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
