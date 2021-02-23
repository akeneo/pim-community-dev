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

use Akeneo\AssetManager\Common\Fake\InMemoryFileExists;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
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
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class ValidateMassEditActionTest extends ControllerIntegrationTestCase
{
    private const MASS_EDIT_ASSETS_ROUTE = 'akeneo_asset_manager_asset_validate_mass_edit_rest';

    private WebClientHelper $webClientHelper;
    private FixturesLoader $fixturesLoader;
    private InMemoryFileExists $fileExists;
    private InMemoryFindActivatedLocalesByIdentifiers $activatedLocales;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixturesLoader = $this->get('akeneo_assetmanager.common.helper.fixtures_loader');
        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
        $this->activatedLocales = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_activated_locales_by_identifiers');
        $this->fileExists = $this->get('akeneo_assetmanager.infrastructure.persistence.query.file_exists');
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
                    'updaters' => [[
                        'id' => 'an_uuid',
                        'attribute' => 'label_designer_d00de54460082b239164135175588647',
                        'channel' => null,
                        'locale' => 'fr_FR',
                        'data' => 'My new data',
                        'action' => 'replace',
                    ],
                    [
                        'id' => 'another_uuid',
                        'attribute' => 'media_designer_9a6a7b91c08be7574d5f48dea2ea99fa',
                        'channel' => null,
                        'locale' => null,
                        'data' => [
                            'filePath' => '/a/b/c/title_12.png',
                            'updatedAt' => '2019-11-22T15:16:21+0000'
                        ],
                        'action' => 'replace',
                    ],
                ],
            ],
        );

        $this->webClientHelper->assert200Ok($this->client->getResponse());
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

        Assert::assertEquals(
            $content[0]['propertyPath'],
            'updaters.some_uuid'
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
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
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
        $this->fixturesLoader->assetFamily('designer')->load();
        $this->fileExists->save('/a/b/c/title_12.png');
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

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
}
