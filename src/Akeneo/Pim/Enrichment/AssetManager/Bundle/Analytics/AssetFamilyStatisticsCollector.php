<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\Analytics;

use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerAssetFamily\SqlLocalizableOnly;
use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerAssetFamily\SqlScopableAndLocalizable;
use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerAssetFamily\SqlScopableOnly;
use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\ElasticSearchAverageMaxNumberOfAssetsPerAssetFamily;
use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfAttributesPerAssetFamily;
use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfValuesPerAsset;
use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\SqlCountAssetFamilies;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class AssetFamilyStatisticsCollector implements DataCollectorInterface
{
    /** @var SqlCountAssetFamilies */
    private $countAssetFamilies;

    /** @var ElasticSearchAverageMaxNumberOfAssetsPerAssetFamily */
    private $averageMaxNumberOfAssetsPerAssetFamily;

    /** @var SqlAverageMaxNumberOfValuesPerAsset */
    private $averageMaxNumberOfValuesPerAsset;

    /** @var SqlAverageMaxNumberOfAttributesPerAssetFamily */
    private $averageMaxNumberOfAttributesPerAssetFamily;

    /** @var SqlLocalizableOnly */
    private $localizableOnly;

    /** @var SqlScopableOnly */
    private $scopableOnly;

    /** @var SqlScopableAndLocalizable */
    private $scopableAndLocalizable;

    public function __construct(
        SqlCountAssetFamilies $countAssetFamilies,
        ElasticSearchAverageMaxNumberOfAssetsPerAssetFamily $averageMaxNumberOfAssetsPerAssetFamily,
        SqlAverageMaxNumberOfValuesPerAsset $averageMaxNumberOfValuesPerAsset,
        SqlAverageMaxNumberOfAttributesPerAssetFamily $averageMaxNumberOfAttributesPerAssetFamily,
        SqlLocalizableOnly $localizableOnly,
        SqlScopableOnly $scopableOnly,
        SqlScopableAndLocalizable $scopableAndLocalizable
    ) {
        $this->countAssetFamilies = $countAssetFamilies;
        $this->averageMaxNumberOfAssetsPerAssetFamily = $averageMaxNumberOfAssetsPerAssetFamily;
        $this->averageMaxNumberOfValuesPerAsset = $averageMaxNumberOfValuesPerAsset;
        $this->averageMaxNumberOfAttributesPerAssetFamily = $averageMaxNumberOfAttributesPerAssetFamily;
        $this->localizableOnly = $localizableOnly;
        $this->scopableOnly = $scopableOnly;
        $this->scopableAndLocalizable = $scopableAndLocalizable;
    }

    public function collect(): array
    {
        $averageMaxNumberOfAssetsPerAssetFamily = $this->averageMaxNumberOfAssetsPerAssetFamily->fetch();
        $averageMaxNumberOfAttributesPerAssetFamily = $this->averageMaxNumberOfAttributesPerAssetFamily->fetch();

        return [
            'nb_asset_families' => $this->countAssetFamilies->fetch()->getVolume(),
            'max_number_of_assets_per_asset_family' => $averageMaxNumberOfAssetsPerAssetFamily->getMaxVolume(),
            'average_number_of_assets_per_asset_family' => $averageMaxNumberOfAssetsPerAssetFamily->getAverageVolume(),
            'average_number_of_values_per_assets' => $this->averageMaxNumberOfValuesPerAsset->fetch()->getAverageVolume(),
            'max_number_of_attributes_per_asset_family' => $averageMaxNumberOfAttributesPerAssetFamily->getMaxVolume(),
            'average_number_of_attributes_per_asset_family' => $averageMaxNumberOfAttributesPerAssetFamily->getAverageVolume(),
            'average_percentage_localizable_only_attributes' => $this->localizableOnly->fetch()->getAverageVolume(),
            'average_percentage_scopable_only_attributes' => $this->scopableOnly->fetch()->getAverageVolume(),
            'average_percentage_scopable_and_localizable_attributes' => $this->scopableAndLocalizable->fetch()->getAverageVolume(),
        ];
    }
}
