<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AverageMaxVolumesNormalizer
{
    private const VOLUME_TYPE = 'average_max';

    /**
     * @param AverageMaxVolumes $data
     *
     * @return array
     */
    public function normalize(AverageMaxVolumes $data): array
    {
        $data = [
            $data->getVolumeName() => [
                'value' => [
                    'average' => $data->getAverageVolume(),
                    'max' => $data->getMaxVolume(),
                ],
                'has_warning' => $data->hasWarning(),
                'type' => self::VOLUME_TYPE
            ]
        ];

        return $data;
    }
}
