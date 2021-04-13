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

namespace Akeneo\AssetManager\Integration\UI\Web\Asset;

use Akeneo\AssetManager\Common\Fake\MassDeleteAssetsLauncherSpy;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class MassDeleteAssetsActionTest extends ControllerIntegrationTestCase
{
    private const MASS_DELETE_ASSETS_ROUTE = 'akeneo_asset_manager_asset_mass_delete_rest';

    private WebClientHelper $webClientHelper;
    private MassDeleteAssetsLauncherSpy $massDeleteAssetsLauncherSpy;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
        $this->massDeleteAssetsLauncherSpy = $this->get('akeneo_assetmanager.infrastructure.job.mass_delete_launcher');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_deletes_all_assets(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::MASS_DELETE_ASSETS_ROUTE,
            [
                'assetFamilyIdentifier' => 'atmosphere',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ],
            [
                "page" => 0,
                "size" => 50,
                "locale" => "en_US",
                "channel" => "ecommerce",
                "filters" => [
                    [
                        "field" => "asset_family",
                        "value" => "atmosphere",
                        "context" => [],
                        "operator" => "="
                    ]
                ]
            ],
        );

        $this->webClientHelper->assert202Accepted($this->client->getResponse());
        $this->massDeleteAssetsLauncherSpy->hasLaunchedMassDelete(
            'atmosphere',
            AssetQuery::createFromNormalized([
                "page" => 0,
                "size" => 50,
                "locale" => "en_US",
                "channel" => "ecommerce",
                "filters" => [
                    [
                        "field" => "asset_family",
                        "value" => "atmosphere",
                        "context" => [],
                        "operator" => "="
                    ]
                ]
            ])
        );
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::MASS_DELETE_ASSETS_ROUTE,
            [
                'assetFamilyIdentifier' => 'designer',
            ],
            'DELETE'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_delete_assets()
    {
        $this->revokeDeletionRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::MASS_DELETE_ASSETS_ROUTE,
            [
                'assetFamilyIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );
        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_asset_family_identifiers_are_not_synced()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::MASS_DELETE_ASSETS_ROUTE,
            [
                'assetFamilyIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ],
            [
                "page" => 0,
                "size" => 50,
                "locale" => "en_US",
                "channel" => "ecommerce",
                "filters" => [
                    [
                        "field" => "asset_family",
                        "value" => "atmosphere",
                        "context" => [],
                        "operator" => "="
                    ]
                ]
            ]
        );
        $this->webClientHelper->assert400BadRequest($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_edit_the_asset_family()
    {
        $this->forbidsEdit();
        $this->webClientHelper->callRoute(
            $this->client,
            self::MASS_DELETE_ASSETS_ROUTE,
            [
                'assetFamilyIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );
        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    private function forbidsEdit(): void
    {
        $this->get('akeneo_assetmanager.application.asset_family_permission.can_edit_asset_family_query_handler')
            ->forbid();
    }
    private function loadFixtures(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_asset_delete', true);
    }

    private function revokeDeletionRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_asset_delete', false);
    }
}
