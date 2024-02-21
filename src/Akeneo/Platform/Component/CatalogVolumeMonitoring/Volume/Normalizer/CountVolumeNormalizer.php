<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountVolumeNormalizer
{
    private const VOLUME_TYPE = 'count';
    /**
     * @param CountVolume $data
     *
     * @return array
     */
    public function normalize(CountVolume $data): array
    {
        $data = [
            $data->getVolumeName() => [
                'value' => $data->getVolume(),
                'type' => self::VOLUME_TYPE
            ]
        ];

        return $data;
    }
}
