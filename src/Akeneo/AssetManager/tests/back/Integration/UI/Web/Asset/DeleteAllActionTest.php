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

use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class DeleteAllActionTest extends ControllerIntegrationTestCase
{
    private const DELETE_ALL_ASSETS_ROUTE = 'akeneo_asset_manager_asset_delete_all_rest';

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var FixturesLoader */
    private $fixturesLoader;

    public function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('Remove this class');

        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
        $this->fixturesLoader = $this->get('akeneo_assetmanager.common.helper.fixtures_loader');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_deletes_all_assets(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/DeleteAll/ok.json');
    }

    /** @test */
    public function it_returns_an_error_when_the_user_does_not_have_the_rights()
    {
        $this->revokeDeletionRights();
        $this->webClientHelper->assertRequest($this->client, 'Asset/DeleteAll/forbidden.json');
    }

    /** @test */
    public function it_does_not_return_an_error_when_the_asset_family_does_not_exist()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/DeleteAll/delete_not_found.json');
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ALL_ASSETS_ROUTE,
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
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_edit_the_asset_family()
    {
        $this->forbidsEdit();
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ALL_ASSETS_ROUTE,
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
        $this->fixturesLoader->assetFamily('designer')->load();
        $this->fixturesLoader->assetFamily('brand')->load();

        $assetRepository = $this->getAssetRepository();
        $entityIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetCode = AssetCode::fromString('starck');
        $assetIdentifier = $assetRepository->nextIdentifier($entityIdentifier, $assetCode);
        $assetItem = $this->createAsset($entityIdentifier, $assetCode, $assetIdentifier);
        $assetRepository->create($assetItem);

        $entityIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetCode = AssetCode::fromString('dyson');
        $assetIdentifier = $assetRepository->nextIdentifier($entityIdentifier, $assetCode);
        $assetItem = $this->createAsset($entityIdentifier, $assetCode, $assetIdentifier);
        $assetRepository->create($assetItem);

        $entityIdentifier = AssetFamilyIdentifier::fromString('brand');
        $assetCode = AssetCode::fromString('cogip');
        $assetIdentifier = $assetRepository->nextIdentifier($entityIdentifier, $assetCode);
        $assetItem = $this->createAsset($entityIdentifier, $assetCode, $assetIdentifier);
        $assetRepository->create($assetItem);

        $entityIdentifier = AssetFamilyIdentifier::fromString('brand');
        $assetCode = AssetCode::fromString('sbep');
        $assetIdentifier = $assetRepository->nextIdentifier($entityIdentifier, $assetCode);
        $assetItem = $this->createAsset($entityIdentifier, $assetCode, $assetIdentifier);
        $assetRepository->create($assetItem);

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_assets_delete_all', true);
    }

    private function createAsset(
        AssetFamilyIdentifier $entityIdentifier,
        AssetCode $assetCode,
        AssetIdentifier $assetIdentifier
    ): Asset {
        return Asset::create(
            $assetIdentifier,
            $entityIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );
    }

    private function getAssetRepository(): AssetRepositoryInterface
    {
        return $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
    }

    private function revokeDeletionRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_assets_delete_all', false);
    }
}
