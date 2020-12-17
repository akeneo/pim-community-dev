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

namespace Akeneo\AssetManager\Integration\PublicApi\Enrich;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\MediaLinkData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\SqlGetAssetMainMediaValues;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlGetAssetMainMediaValuesTest extends SqlIntegrationTestCase
{
    private SqlGetAssetMainMediaValues $sqlGetAssetMainMediaValues;
    private string $attributeMainMediaIdentifier;

    public function setUp(): void
    {
        parent::setUp();

        $this->sqlGetAssetMainMediaValues = $this->get('akeneo_assetmanager.infrastructure.persistence.query.enrich.get_asset_main_media_values_public_api');
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
        $this->loadDataset();
    }

    /** @test */
    public function it_returns_a_list_of_main_media_values(): void
    {
        $results = $this->sqlGetAssetMainMediaValues->forAssetFamilyAndAssetCodes(
            'asset_family_1',
            ['asset_1_a', 'asset_1_b', 'asset_2_a']
        );

        self::assertArrayHasKey('asset_1_a', $results);
        self::assertCount(2, $results['asset_1_a']);
        self::assertEqualsCanonicalizing([
            [
                'data' => 'http://www.example.com/us_link_1_a',
                'locale' => 'en_US',
                'channel' => null,
                'attribute' => $this->attributeMainMediaIdentifier,
            ],
            [
                'data' => 'http://www.example.com/fr_link_1_a',
                'locale' => 'fr_FR',
                'channel' => null,
                'attribute' => $this->attributeMainMediaIdentifier,
            ],
        ], $results['asset_1_a']);

        self::assertArrayHasKey('asset_1_b', $results);
        self::assertEqualsCanonicalizing([
            [
                'data' => 'http://www.example.com/us_link_1_b',
                'locale' => 'en_US',
                'channel' => null,
                'attribute' => $this->attributeMainMediaIdentifier,
            ],
            [
                'data' => 'http://www.example.com/fr_link_1_b',
                'locale' => 'fr_FR',
                'channel' => null,
                'attribute' => $this->attributeMainMediaIdentifier,
            ],
        ], $results['asset_1_b']);

        self::assertArrayNotHasKey('asset_2_a', $results);
    }

    /** @test */
    public function it_returns_an_empty_array_for_unknown_asset_family_or_asset_codes(): void
    {
        $results = $this->sqlGetAssetMainMediaValues->forAssetFamilyAndAssetCodes(
            'asset_family_1',
            ['asset_2_a']
        );
        self::assertEmpty($results);

        $results = $this->sqlGetAssetMainMediaValues->forAssetFamilyAndAssetCodes(
            'unknown',
            ['asset_1_a', 'asset_2_a']
        );
        self::assertEmpty($results);

        $results = $this->sqlGetAssetMainMediaValues->forAssetFamilyAndAssetCodes(
            'asset_family_1',
            []
        );
        self::assertEmpty($results);
    }

    private function loadDataset(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');

        foreach ([1, 2] as $index) {
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(sprintf('asset_family_%d', $index));
            $assetFamilyRepository->create(AssetFamily::create(
                $assetFamilyIdentifier,
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty())
            );
            $assetFamily = $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);

            if (1 === $index) {
                $this->attributeMainMediaIdentifier = $assetFamily->getAttributeAsMainMediaReference()->getIdentifier()->__toString();
            }

            foreach (range('a', 'b') as $assetCode) {
                $assetRepository->create(
                    Asset::create(
                        AssetIdentifier::fromString(sprintf('asset_%d_%s', $index, $assetCode)),
                        $assetFamilyIdentifier,
                        AssetCode::fromString(sprintf('asset_%d_%s', $index, $assetCode)),
                        ValueCollection::fromValues([
                            Value::create(
                                $assetFamily->getAttributeAsMainMediaReference()->getIdentifier(),
                                ChannelReference::noReference(),
                                LocaleReference::createFromNormalized('en_US'),
                                MediaLinkData::fromString(sprintf('http://www.example.com/us_link_%d_%s', $index, $assetCode))
                            ),
                            Value::create(
                                $assetFamily->getAttributeAsMainMediaReference()->getIdentifier(),
                                ChannelReference::noReference(),
                                LocaleReference::createFromNormalized('fr_FR'),
                                MediaLinkData::fromString(sprintf('http://www.example.com/fr_link_%d_%s', $index, $assetCode))
                            ),
                        ])
                    )
                );
            }
        }
    }
}
