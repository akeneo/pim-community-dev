<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\UI\Web\AssetFamily;

use Akeneo\AssetManager\Common\Fake\ProductLinkRuleLauncherSpy;
use Akeneo\AssetManager\Common\Fake\SecurityFacadeStub;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ExecuteProductLinkRulesActionTest extends ControllerIntegrationTestCase
{
    private const EXECUTE_PRODUCT_LINK_RULES_ROUTE = 'akeneo_asset_manager_asset_family_execute_product_link_rules';
    private const EXECUTE_PRODUCT_LINK_RULES_ACL = 'akeneo_assetmanager_asset_family_execute_product_link_rule';
    private const EDIT_ASSET_FAMILY_ACL = 'akeneo_assetmanager_asset_family_edit';

    private WebClientHelper $webClientHelper;

    private ProductLinkRuleLauncherSpy $productLinkRuleLauncherSpy;

    private SecurityFacadeStub $securityFacade;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
        $this->productLinkRuleLauncherSpy = $this->get('akeneo_assetmanager.infrastructure.job.product_link_rule_launcher');
        $this->securityFacade = $this->get('oro_security.security_facade');
        $this->loadFixtures();
    }

    /** @test */
    public function it_launches_product_link_rules_for_given_assets(): void
    {
        $this->allowExecuteRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::EXECUTE_PRODUCT_LINK_RULES_ROUTE,
            ['identifier' => 'singer'],
            'POST'
        );
        Assert::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
        $this->productLinkRuleLauncherSpy->assertHasRunForAssetsInSameLaunch('singer', ['celine_dion', 'mariah_carey']);
    }

    /** @test */
    public function it_does_not_launch_product_link_rules_when_asset_family_is_not_found(): void
    {
        $this->allowExecuteRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::EXECUTE_PRODUCT_LINK_RULES_ROUTE,
            ['identifier' => 'unknown'],
            'POST'
        );
        Assert::assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->productLinkRuleLauncherSpy->assertHasNoRun();
    }

    /** @test */
    public function it_forbids_to_launch_product_link_rules(): void
    {
        $this->allowExecuteRights();
        $this->revokeExecuteProductLinkRulesRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::EXECUTE_PRODUCT_LINK_RULES_ROUTE,
            ['identifier' => 'singer'],
            'POST'
        );
        Assert::assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
        $this->productLinkRuleLauncherSpy->assertHasNoRun();

        $this->allowExecuteRights();
        $this->revokeEditAsstFamilyRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::EXECUTE_PRODUCT_LINK_RULES_ROUTE,
            ['identifier' => 'singer'],
            'POST'
        );
        Assert::assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
        $this->productLinkRuleLauncherSpy->assertHasNoRun();
    }

    private function loadFixtures(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');

        $entityItem = AssetFamily::create(
            AssetFamilyIdentifier::fromString('singer'),
            ['en_US' => 'Singer', 'fr_FR' => 'Chanteur'],
            Image::createEmpty(),
            RuleTemplateCollection::createFromProductLinkRules([
                [
                    'product_selections' => [
                        [
                            'field' => '{{category_field}}',
                            'operator' => Operators::EQUALS,
                            'value' => '{{category}}',
                        ],
                    ],
                    'assign_assets_to' => [
                        [
                            'mode' => 'add',
                            'attribute' => '{{product_multiple_link}}',
                        ],
                    ],
                ],
            ])
        );
        $assetFamilyRepository->create($entityItem);

        $celineAsset = Asset::create(
            AssetIdentifier::create('singer', 'celine_dion', 'fingerprint'),
            AssetFamilyIdentifier::fromString('singer'),
            AssetCode::fromString('celine_dion'),
            ValueCollection::fromValues([])
        );
        $assetRepository->create($celineAsset);
        $celineAsset = Asset::create(
            AssetIdentifier::create('singer', 'mariah_carey', 'fingerprint'),
            AssetFamilyIdentifier::fromString('singer'),
            AssetCode::fromString('mariah_carey'),
            ValueCollection::fromValues([])
        );
        $assetRepository->create($celineAsset);
    }

    private function allowExecuteRights(): void
    {
        $this->securityFacade->setIsGranted(self::EDIT_ASSET_FAMILY_ACL, true);
        $this->securityFacade->setIsGranted(self::EXECUTE_PRODUCT_LINK_RULES_ACL, true);
    }

    private function revokeEditAsstFamilyRights(): void
    {
        $this->securityFacade->setIsGranted(self::EDIT_ASSET_FAMILY_ACL, false);
    }

    private function revokeExecuteProductLinkRulesRights(): void
    {
        $this->securityFacade->setIsGranted(self::EXECUTE_PRODUCT_LINK_RULES_ACL, false);
    }
}
