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

namespace Akeneo\AssetManager\Integration\PublicApi\Analytics;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfAssetsPerAssetFamily;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAverageMaxNumberOfAssetsPerAssetFamilyTest extends SqlIntegrationTestCase
{
    /** @var SqlAverageMaxNumberOfAssetsPerAssetFamily */
    private $averageMaxNumberOfAssetsPerAssetFamily;

    public function setUp(): void
    {
        parent::setUp();

        $this->averageMaxNumberOfAssetsPerAssetFamily = $this->get('akeneo_assetmanager.infrastructure.persistence.query.analytics.average_max_number_of_assets_per_asset_family');
        $this->resetDB();
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_returns_the_average_and_max_number_of_assets_per_asset_family()
    {
        $this->loadAssetsForAssetFamily(2);
        $this->loadAssetsForAssetFamily(4);
        $this->loadAssetsForAssetFamily(0);

        $volume = $this->averageMaxNumberOfAssetsPerAssetFamily->fetch();

        $this->assertEquals('4', $volume->getMaxVolume());
        $this->assertEquals('2', $volume->getAverageVolume());
    }

    private function loadAssetsForAssetFamily(int $numberOfAssetsPerAssetFamiliestoLoad): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($this->getRandomIdentifier());
        $assetFamilyRepository->create(AssetFamily::create(
            $assetFamilyIdentifier,
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        ));

        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');

        for ($i = 0; $i < $numberOfAssetsPerAssetFamiliestoLoad; $i++) {
            $assetRepository->create(
                Asset::create(
                    AssetIdentifier::fromString(sprintf('%s', $this->getRandomIdentifier())),
                    $assetFamilyIdentifier,
                    AssetCode::fromString(sprintf('%s_%d', $i, $assetFamilyIdentifier->normalize())),
                    ValueCollection::fromValues([])
                )
            );
        }
    }

    private function getRandomIdentifier(): string
    {
        return str_replace('-', '_', Uuid::uuid4()->toString());
    }
}
