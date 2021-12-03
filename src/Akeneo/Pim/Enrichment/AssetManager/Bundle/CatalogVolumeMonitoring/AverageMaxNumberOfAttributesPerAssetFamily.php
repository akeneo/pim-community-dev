<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\CatalogVolumeMonitoring;

use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfAttributesPerAssetFamily;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AverageMaxNumberOfAttributesPerAssetFamily implements AverageMaxQuery
{
    private const VOLUME_NAME = 'average_max_attributes_per_asset_family';

    /** @var SqlAverageMaxNumberOfAttributesPerAssetFamily */
    private $averageMaxNumberOfAttributesPerAssetFamily;

    public function __construct(
        SqlAverageMaxNumberOfAttributesPerAssetFamily $averageMaxNumberOfAttributesPerAssetFamily
    ) {
        $this->averageMaxNumberOfAttributesPerAssetFamily = $averageMaxNumberOfAttributesPerAssetFamily;
    }

    public function fetch(): AverageMaxVolumes
    {
        $volume = $this->averageMaxNumberOfAttributesPerAssetFamily->fetch();
        $result = new AverageMaxVolumes(
            $volume->getMaxVolume(),
            $volume->getAverageVolume(),
            self::VOLUME_NAME
        );

        return $result;
    }
}
