<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\UI\Web\Asset;

use Akeneo\AssetManager\Common\Helper\AuthenticatedClient;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class DeleteActionTest extends ControllerIntegrationTestCase
{
    private const DELETE_ASSET_ROUTE = 'akeneo_asset_manager_asset_delete_rest';

    private FixturesLoader $fixturesLoader;

    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
        $this->fixturesLoader = $this->get('akeneo_assetmanager.common.helper.fixtures_loader');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_deletes_a_asset_and_its_values(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Delete/ok.json');
    }

    /** @test */
    public function it_returns_an_error_when_the_user_does_not_have_the_rights()
    {
        $this->revokeDeletionRights();
        $this->webClientHelper->assertRequest($this->client, 'Asset/Delete/forbidden.json');
    }

    /** @test */
    public function it_returns_an_error_when_the_asset_does_not_exist()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Delete/delete_not_found.json');
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ASSET_ROUTE,
            [
                'assetCode' => 'name',
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
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_edit_the_asset_family()
    {
        $this->forbidsEdit();
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ASSET_ROUTE,
            [
                'assetCode' => 'name',
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
        $this->fixturesLoader->assetFamily('designer')->load();
        $this->createAssets();

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_asset_delete', true);
    }

    private function getAssetRepository(): AssetRepositoryInterface
    {
        return $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
    }

    private function revokeDeletionRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_asset_delete', false);
    }

    private function createAssets(): void
    {
        $assetRepository = $this->getAssetRepository();
        $assetItem = Asset::create(
            AssetIdentifier::create('designer', 'starck', md5('fingerprint')),
            AssetFamilyIdentifier::fromString('designer'),
            AssetCode::fromString('starck'),
            ValueCollection::fromValues([])
        );
        $assetRepository->create($assetItem);
    }
}
