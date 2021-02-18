<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\CatalogVolumeMonitoring;

use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\ElasticSearchAverageMaxNumberOfAssetsPerAssetFamily;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AverageMaxNumberOfAssetsPerAssetFamily implements AverageMaxQuery
{
    private const VOLUME_NAME = 'average_max_assets_per_asset_family';

    private ElasticSearchAverageMaxNumberOfAssetsPerAssetFamily $averageMaxNumberOfAssetsPerAssetFamily;
    private int $limit;

    public function __construct(
        ElasticSearchAverageMaxNumberOfAssetsPerAssetFamily $averageMaxNumberOfAssetsPerAssetFamily,
        int $limit
    ) {
        $this->averageMaxNumberOfAssetsPerAssetFamily = $averageMaxNumberOfAssetsPerAssetFamily;
        $this->limit = $limit;
    }

    public function fetch(): AverageMaxVolumes
    {
        $volume = $this->averageMaxNumberOfAssetsPerAssetFamily->fetch();
        $result = new AverageMaxVolumes(
            $volume->getMaxVolume(),
            $volume->getAverageVolume(),
            $this->limit,
            self::VOLUME_NAME
        );

        return $result;
    }
}
