<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\UI\Web\AssetFamily;

use Akeneo\AssetManager\Common\Fake\NamingConventionLauncherSpy;
use Akeneo\AssetManager\Common\Fake\SecurityFacadeStub;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ExecuteNamingConventionActionTest extends ControllerIntegrationTestCase
{
    private const EXECUTE_NAMING_CONVENTION_ROUTE = 'akeneo_asset_manager_asset_family_execute_naming_convention';
    private const EXECUTE_NAMING_CONVENTION_ACL = 'akeneo_assetmanager_asset_family_execute_naming_conventions';
    private const EDIT_ASSET_FAMILY_ACL = 'akeneo_assetmanager_asset_family_edit';

    private WebClientHelper $webClientHelper;

    private NamingConventionLauncherSpy $namingConventionLauncherSpy;

    private SecurityFacadeStub $securityFacade;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
        $this->namingConventionLauncherSpy = $this->get('akeneo_assetmanager.infrastructure.job.naming_convention_launcher');
        $this->securityFacade = $this->get('oro_security.security_facade');
        $this->securityFacade->setIsGranted(self::EDIT_ASSET_FAMILY_ACL, true);
        $this->securityFacade->setIsGranted(self::EXECUTE_NAMING_CONVENTION_ACL, true);
        $this->loadFixtures();
    }

    /** @test */
    public function it_launches_naming_convention(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::EXECUTE_NAMING_CONVENTION_ROUTE,
            ['assetFamilyIdentifier' => 'singer'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
        $this->namingConventionLauncherSpy->assertHasJobForAssetFamily('singer');
    }

    /** @test */
    public function it_does_not_launch_naming_convention_when_asset_family_is_not_found(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::EXECUTE_NAMING_CONVENTION_ROUTE,
            ['assetFamilyIdentifier' => 'unknown'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->namingConventionLauncherSpy->assertHasNoJob();
    }

    /** @test */
    public function it_requires_acl_on_edit_asset_family(): void
    {
        $this->securityFacade->setIsGranted(self::EDIT_ASSET_FAMILY_ACL, false);

        $this->webClientHelper->callRoute(
            $this->client,
            self::EXECUTE_NAMING_CONVENTION_ROUTE,
            ['assetFamilyIdentifier' => 'unknown'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        Assert::assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
        $this->namingConventionLauncherSpy->assertHasNoJob();
    }

    /** @test */
    public function it_requires_acl_on_execute_naming_convention(): void
    {
        $this->securityFacade->setIsGranted(self::EXECUTE_NAMING_CONVENTION_ACL, false);

        $this->webClientHelper->callRoute(
            $this->client,
            self::EXECUTE_NAMING_CONVENTION_ROUTE,
            ['assetFamilyIdentifier' => 'unknown'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        Assert::assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
        $this->namingConventionLauncherSpy->assertHasNoJob();
    }

    private function loadFixtures(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');

        $namingConvention = NamingConvention::createFromNormalized([
            'source' => [
                'property' => 'media',
                'locale' => null,
                'channel' => null,
            ],
            'pattern' => '/(pattern)/',
            'abort_asset_creation_on_error' => false,
        ]);

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('singer'),
            ['en_US' => 'Singer', 'fr_FR' => 'Chanteur'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamily = $assetFamily->withNamingConvention($namingConvention);
        $assetFamilyRepository->create($assetFamily);
    }
}
