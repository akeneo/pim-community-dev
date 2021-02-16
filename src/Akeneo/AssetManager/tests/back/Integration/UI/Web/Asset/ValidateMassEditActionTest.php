<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\UI\Web\Asset;

use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class MassDeleteAssetsActionTest extends ControllerIntegrationTestCase
{
    private const MASS_EDIT_ASSETS_ROUTE = 'akeneo_asset_manager_asset_validate_mass_edit_rest';

    private WebClientHelper $webClientHelper;
    private FixturesLoader $fixturesLoader;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixturesLoader = $this->get('akeneo_assetmanager.common.helper.fixtures_loader');
        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
        $this->massDeleteAssetsLauncherSpy = $this->get('akeneo_assetmanager.infrastructure.job.mass_delete_launcher');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_validates_mass_edit_action(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::MASS_EDIT_ASSETS_ROUTE,
            [
                'assetFamilyIdentifier' => 'atmosphere',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json'
            ],
            [
                'query' => [
                    'page' => 0,
                    'size' => 50,
                    'locale' => 'en_US',
                    'channel' => 'ecommerce',
                    'filters' => [
                        [
                            'field' => 'asset_family',
                            'value' => 'atmosphere',
                            'context' => [],
                            'operator' => '='
                        ]
                    ]
                ],
                'type' => 'edit',
                'updaters' => []
            ],
        );

        $this->webClientHelper->assert202Accepted($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_return_errors_if_invalid_mass_edit_action(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::MASS_EDIT_ASSETS_ROUTE,
            [
                'assetFamilyIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json'
            ],
            [
                'query' => [
                    'page' => 0,
                    'size' => 50,
                    'locale' => 'en_US',
                    'channel' => 'ecommerce',
                    'filters' => [
                        [
                            'field' => 'asset_family',
                            'value' => 'designer',
                            'context' => [],
                            'operator' => '='
                        ]
                    ]
                ],
                'type' => 'edit',
                'updaters' => [
                    [
                        'attribute' => 'name_designer_fingerprint',
                        'channel' => null,
                        'locale' => 'en_US',
                        'data' => 'new label',
                        'action' => 'set',
                        'id' => 'some_uuid'
                    ]
                ]
            ],
        );

        $this->webClientHelper->assert400BadRequest($this->client->getResponse());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        Assert::assertEquals(
            $content[0]['messageTemplate'],
            'This value is too long. It should have 2 characters or less.'
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
            self::MASS_EDIT_ASSETS_ROUTE,
            [
                'assetFamilyIdentifier' => 'designer',
            ],
            'POST',
            [
                'CONTENT_TYPE' => 'application/json'
            ],
            [
                'query' => [
                    'page' => 0,
                    'size' => 50,
                    'locale' => 'en_US',
                    'channel' => 'ecommerce',
                    'filters' => [
                        [
                            'field' => 'asset_family',
                            'value' => 'atmosphere',
                            'context' => [],
                            'operator' => '='
                        ]
                    ]
                ],
                'type' => 'edit',
                'updaters' => []
            ],
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_delete_assets()
    {
        $this->revokeEditionRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::MASS_EDIT_ASSETS_ROUTE,
            [
                'assetFamilyIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json'
            ],
            [
                'query' => [
                    'page' => 0,
                    'size' => 50,
                    'locale' => 'en_US',
                    'channel' => 'ecommerce',
                    'filters' => [
                        [
                            'field' => 'asset_family',
                            'value' => 'atmosphere',
                            'context' => [],
                            'operator' => '='
                        ]
                    ]
                ],
                'type' => 'edit',
                'updaters' => []
            ],
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
            self::MASS_EDIT_ASSETS_ROUTE,
            [
                'assetFamilyIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json'
            ],
            [
                'query' => [
                    'page' => 0,
                    'size' => 50,
                    'locale' => 'en_US',
                    'channel' => 'ecommerce',
                    'filters' => [
                        [
                            'field' => 'asset_family',
                            'value' => 'atmosphere',
                            'context' => [],
                            'operator' => '='
                        ]
                    ]
                ],
                'type' => 'edit',
                'updaters' => []
            ],
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
            self::MASS_EDIT_ASSETS_ROUTE,
            [
                'assetFamilyIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json'
            ],
            [
                'query' => [
                    'page' => 0,
                    'size' => 50,
                    'locale' => 'en_US',
                    'channel' => 'ecommerce',
                    'filters' => [
                        [
                            'field' => 'asset_family',
                            'value' => 'designer',
                            'context' => [],
                            'operator' => '='
                        ]
                    ]
                ],
                'type' => 'edit',
                'updaters' => []
            ],
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
        $this->fixturesLoader->assetFamily('designer')->load();

        // text attribute
        $textAttributeIdentifier = AttributeIdentifier::create('designer', 'name', 'fingerprint');
        $textAttribute = TextAttribute::createText(
            $textAttributeIdentifier,
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(2),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->create($textAttribute);
    }

    private function revokeEditionRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_asset_delete', false);
    }
}
