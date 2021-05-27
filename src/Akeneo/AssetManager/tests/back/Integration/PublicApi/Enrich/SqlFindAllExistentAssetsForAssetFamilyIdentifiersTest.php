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

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\SqlFindAllExistentAssetsForAssetFamilyIdentifiers;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlFindAllExistentAssetsForAssetFamilyIdentifiersTest extends SqlIntegrationTestCase
{
    private SqlFindAllExistentAssetsForAssetFamilyIdentifiers $findAllExistentAssetForAssetFamilyIdentifiers;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAllExistentAssetForAssetFamilyIdentifiers = $this->get('akeneo_assetmanager.infrastructure.persistence.query.enrich.find_all_existent_assets_for_asset_family_identifiers_public_api');
        $this->resetDB();
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    public function test_it_returns_nothing_with_empty_arguments(): void
    {
        $expected = [];
        $actual = $this->findAllExistentAssetForAssetFamilyIdentifiers->forAssetFamilyIdentifiersAndAssetCodes([]);

        Assert::assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_returns_nothing_no_asset_codes_for_asset_family(): void
    {
        $expected = [];
        $actual = $this->findAllExistentAssetForAssetFamilyIdentifiers->forAssetFamilyIdentifiersAndAssetCodes(
            [
                'asset_family_code' => []
            ]
        );

        Assert::assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_returns_only_the_matching_results(): void
    {
        $this->loadDataset();

        $expected = [
            'asset_family_1' => ['asset_a', 'asset_c', 'asset_family_1_asset_unique'],
            'asset_family_2' => ['asset_a', 'asset_b']
        ];
        $actual = $this
            ->findAllExistentAssetForAssetFamilyIdentifiers
            ->forAssetFamilyIdentifiersAndAssetCodes(
                [
                    'asset_family_1' => ['asset_a', 'asset_c', 'asset_family_1_asset_unique'],
                    'asset_family_2' => ['asset_a', 'asset_b', 'a_non_existing_assets']
                ]
            );

        Assert::assertEqualsCanonicalizing($expected, $actual);
    }

    private function loadDataset(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');

        $assetFamilyIdentifiers = array_map(fn (int $identifier) => AssetFamilyIdentifier::fromString(sprintf('asset_family_%d', $identifier)), range(1, 4));

        foreach ($assetFamilyIdentifiers as $assetFamilyIdentifier) {
            $assetFamilyRepository->create(AssetFamily::create($assetFamilyIdentifier, [], Image::createEmpty(), RuleTemplateCollection::empty()));
        }

        foreach ($assetFamilyIdentifiers as $assetFamilyIdentifier) {
            foreach (range('a', 'e') as $assetCode) {
                $assetRepository->create(
                    Asset::create(
                        AssetIdentifier::fromString(sprintf('asset_%s_%s', $assetCode, $assetFamilyIdentifier->normalize())),
                        $assetFamilyIdentifier,
                        AssetCode::fromString(sprintf('asset_%s', $assetCode)),
                        ValueCollection::fromValues([])
                    )
                );
            }
            $assetRepository->create(
                Asset::create(
                    AssetIdentifier::fromString(sprintf('toto_asset_%s', $assetFamilyIdentifier->normalize())),
                    $assetFamilyIdentifier,
                    AssetCode::fromString(sprintf('%s_asset_unique', $assetFamilyIdentifier->normalize())),
                    ValueCollection::fromValues([])
                )
            );
        }
    }
}
