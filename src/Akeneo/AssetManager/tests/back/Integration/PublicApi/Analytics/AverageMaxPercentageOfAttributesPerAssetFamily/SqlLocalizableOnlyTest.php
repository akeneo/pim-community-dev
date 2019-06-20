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

namespace Akeneo\AssetManager\Integration\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerAssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerAssetFamily\SqlLocalizableOnly;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlLocalizableOnlyTest extends SqlIntegrationTestCase
{
    /** @var SqlLocalizableOnly */
    private $averageMaxPercentageOfLocalizableOnlyAttributessPerAssetFamily;

    /** @var CreateAttributesHelper */
    private $createAttributesHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->averageMaxPercentageOfLocalizableOnlyAttributessPerAssetFamily = $this->get('akeneo_assetmanager.infrastructure.persistence.query.analytics.average_max_percentage_of_attributes_per_asset_family.localizable_only');
        $this->createAttributesHelper = new CreateAttributesHelper($this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute'));
        $this->resetDB();
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_returns_the_average_and_max_percentage_of_localizable_only_attributes_per_asset_family()
    {
        $assetFamilyIdentifier = $this->createAssetFamily();
        $this->createAttributesHelper->loadLocalizableOnlyAttributesForAssetFamily($assetFamilyIdentifier, 4);
        $this->createAttributesHelper->loadLocalizableAndScopableAttributesForAssetFamily($assetFamilyIdentifier,
            10);
        $this->createAttributesHelper->loadScopableOnlyAttributesForAssetFamily($assetFamilyIdentifier, 10);
        $this->createAttributesHelper->loadNotLocalizableNotScopableAttributesForAssetFamily($assetFamilyIdentifier,
            10);

        $anotherAssetFamilyIdentifier = $this->createAssetFamily();
        $this->createAttributesHelper->loadLocalizableOnlyAttributesForAssetFamily($anotherAssetFamilyIdentifier,
            2);
        $this->createAttributesHelper->loadLocalizableAndScopableAttributesForAssetFamily($anotherAssetFamilyIdentifier,
            10);
        $this->createAttributesHelper->loadScopableOnlyAttributesForAssetFamily($anotherAssetFamilyIdentifier,
            10);
        $this->createAttributesHelper->loadNotLocalizableNotScopableAttributesForAssetFamily($anotherAssetFamilyIdentifier,
            10);

        $volume = $this->averageMaxPercentageOfLocalizableOnlyAttributessPerAssetFamily->fetch();

        $this->assertEquals('11', $volume->getMaxVolume());
        $this->assertEquals('10', $volume->getAverageVolume());
    }

    private function createAssetFamily(): AssetFamilyIdentifier
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyIdentifier = $this->getRandomIdentifier();
        $assetFamilyRepository->create(AssetFamily::create(
            $assetFamilyIdentifier,
            [],
            Image::createEmpty()
        ));

        return $assetFamilyIdentifier;
    }

    private function getRandomIdentifier(): AssetFamilyIdentifier
    {
        return AssetFamilyIdentifier::fromString(str_replace('-', '_', Uuid::uuid4()->toString()));
    }
}
