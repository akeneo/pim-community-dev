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

namespace Akeneo\AssetManager\Integration\PublicApi\Enrich;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\FindAssetLabelTranslation;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class FindAssetLabelTranslationTest extends SqlIntegrationTestCase
{
    private FindAssetLabelTranslation $findAssetLabelTranslation;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAssetLabelTranslation = $this->get(
            'akeneo_assetmanager.infrastructure.persistence.query.enrich.find_asset_label_translation_public_api'
        );
        $this->resetDB();
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    public function test_it_returns_nothing_when_with_empty_arguments(): void
    {
        $expected = [];
        $actual = $this->findAssetLabelTranslation->byFamilyCodeAndAssetCodes(
            'unknown_asset_family',
            ['unknown_asset'],
            'fr_FR'
        );

        Assert::assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_returns_the_label_of_the_asset_for_the_given_locale(): void
    {
        $this->loadAssets();


        $actual = $this->findAssetLabelTranslation
            ->byFamilyCodeAndAssetCodes('designer', ['starck', 'michael', 'jacobs'], 'fr_FR');

        $expected = [
            'starck' => 'Philippe Starck',
            'michael' => null,
            'jacobs' => 'Marc Jacobs',
        ];
        Assert::assertEquals($expected, $actual);
    }

    private function loadAssets(): void
    {
        $this->fixturesLoader
            ->assetFamily('designer')
            ->load();

        $this->fixturesLoader
            ->asset('designer', 'starck')
            ->withValues(
                [
                    'label' => [
                        [
                            'channel' => null,
                            'locale' => 'fr_FR',
                            'data' => 'Philippe Starck',
                        ],
                    ],
                ]
            )
            ->load();

        $this->fixturesLoader
            ->asset('designer', 'jacobs')
            ->withValues(
                [
                    'label' => [
                        [
                            'channel' => null,
                            'locale' => 'fr_FR',
                            'data' => 'Marc Jacobs',
                        ],
                    ],
                ]
            )
            ->load();
        $this->fixturesLoader->asset('designer', 'michael')->load();
    }
}
