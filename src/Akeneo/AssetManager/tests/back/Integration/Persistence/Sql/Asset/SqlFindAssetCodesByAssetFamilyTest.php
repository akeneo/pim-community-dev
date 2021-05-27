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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\SqlFindAssetCodesByAssetFamily;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindAssetCodesByAssetFamilyTest extends SqlIntegrationTestCase
{
    private SqlFindAssetCodesByAssetFamily $findAssetCodesByAssetFamily;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAssetCodesByAssetFamily = $this->get(SqlFindAssetCodesByAssetFamily::class);
        $this->resetDB();
        $this->loadAssetFamilyAndAssets();
    }

    /** @test */
    public function it_returns_asset_codes_of_an_asset_family()
    {
        $iterator = $this->findAssetCodesByAssetFamily->find(AssetFamilyIdentifier::fromString('designer'));
        $arrayResults = iterator_to_array($iterator);

        $this->assertCount(2, $arrayResults);
        $this->assertContainsEquals(AssetCode::fromString('starck'), $arrayResults);
        $this->assertContainsEquals(AssetCode::fromString('coco'), $arrayResults);
    }

    /** @test */
    public function it_returns_empty_iterator_for_unknown_asset_family()
    {
        $iterator = $this->findAssetCodesByAssetFamily->find(AssetFamilyIdentifier::fromString('unknown'));
        $this->assertEmpty(iterator_to_array($iterator));
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamilyAndAssets(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamily = AssetFamily::create(
            $assetFamilyIdentifier,
            ['fr_FR' => 'Concepteur', 'en_US' => 'Designer'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);

        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $starkCode = AssetCode::fromString('starck');
        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString('id1'),
                $assetFamilyIdentifier,
                $starkCode,
                ValueCollection::fromValues([])
            )
        );
        $cocoCode = AssetCode::fromString('coco');
        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString('id2'),
                $assetFamilyIdentifier,
                $cocoCode,
                ValueCollection::fromValues([])
            )
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $assetFamily = AssetFamily::create(
            $assetFamilyIdentifier,
            ['fr_FR' => 'packshot', 'en_US' => 'packshot'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);
        $firstCode = AssetCode::fromString('first');
        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString('id3'),
                $assetFamilyIdentifier,
                $firstCode,
                ValueCollection::fromValues([])
            )
        );
    }
}
