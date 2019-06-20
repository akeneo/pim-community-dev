<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\CatalogVolumeMonitoring;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfAttributesPerAssetFamily;

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

    /** @var int */
    private $limit;

    public function __construct(
        SqlAverageMaxNumberOfAttributesPerAssetFamily $averageMaxNumberOfAttributesPerAssetFamily,
        int $limit
    ) {
        $this->averageMaxNumberOfAttributesPerAssetFamily = $averageMaxNumberOfAttributesPerAssetFamily;
        $this->limit = $limit;
    }

    public function fetch(): AverageMaxVolumes
    {
        $volume = $this->averageMaxNumberOfAttributesPerAssetFamily->fetch();
        $result = new AverageMaxVolumes(
            $volume->getMaxVolume(),
            $volume->getAverageVolume(),
            $this->limit,
            self::VOLUME_NAME
        );

        return $result;
    }
}
