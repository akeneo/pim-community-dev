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

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Common\Fake\InMemoryAttributeRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryFileExists;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Common\Fake\MassEditAssetsLauncherSpy;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class MassEditAssetsActionTest extends ControllerIntegrationTestCase
{
    private const MASS_EDIT_ASSETS_ROUTE = 'akeneo_asset_manager_asset_mass_edit_rest';

    private WebClientHelper $webClientHelper;
    private MassEditAssetsLauncherSpy $massEditAssetsLauncherSpy;
    private FixturesLoader $fixturesLoader;
    private InMemoryFileExists $fileExists;
    private InMemoryFindActivatedLocalesByIdentifiers $activatedLocales;
    private InMemoryAttributeRepository $attributeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
        $this->massEditAssetsLauncherSpy = $this->get('akeneo_assetmanager.infrastructure.job.mass_edit_launcher');
        $this->fixturesLoader = $this->get('akeneo_assetmanager.common.helper.fixtures_loader');
        $this->fileExists = $this->get('akeneo_assetmanager.infrastructure.persistence.query.file_exists');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->activatedLocales = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_activated_locales_by_identifiers');
        $this->loadFixture();
    }

    /**
     * @test
     */
    public function it_mass_edit_all_assets(): void
    {
        /** @var TextAttribute $labelAttribute */
        $labelAttribute = $this->attributeRepository->getByIdentifier(
            AttributeIdentifier::fromString('label_designer_d00de54460082b239164135175588647')
        );

        /** @var MediaFileAttribute $mediaAttribute */
        $mediaAttribute = $this->attributeRepository->getByIdentifier(
            AttributeIdentifier::fromString('media_designer_9a6a7b91c08be7574d5f48dea2ea99fa')
        );

        $this->webClientHelper->callRoute(
            $this->client,
            self::MASS_EDIT_ASSETS_ROUTE,
            [
                'assetFamilyIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'type' => 'edit',
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
                            'operator' => '=',
                        ],
                    ],
                ],
                'updaters' => [
                    [
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

        $this->webClientHelper->assert202Accepted($this->client->getResponse());
        $this->massEditAssetsLauncherSpy->hasLaunchedMassEdit(
            'designer',
            AssetQuery::createFromNormalized([
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
            ]),
            [
                new EditTextValueCommand($labelAttribute, null, 'fr_FR', 'My new data'),
                new EditMediaFileValueCommand(
                    $mediaAttribute,
                    null,
                    null,
                    '/a/b/c/title_12.png',
                    null,
                    null,
                    null,
                    null,
                    '2019-11-22T15:16:21+0000'
                ),
            ]
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
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->massEditAssetsLauncherSpy->assertHasNoRun();
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
                'type' => 'edit',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
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
                        ],
                    ],
                ],
            ],
        );
        $this->webClientHelper->assert400BadRequest($this->client->getResponse());
        $this->massEditAssetsLauncherSpy->assertHasNoRun();
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
                'CONTENT_TYPE' => 'application/json',
            ],
            [

            ]
        );
        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
        $this->massEditAssetsLauncherSpy->assertHasNoRun();
    }

    private function forbidsEdit(): void
    {
        $this->get('akeneo_assetmanager.application.asset_family_permission.can_edit_asset_family_query_handler')
            ->forbid();
    }

    private function loadFixture()
    {
        $this->fixturesLoader->assetFamily('designer')->load();
        $this->fileExists->save('/a/b/c/title_12.png');
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
    }
}
