<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer;

use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer\AverageMaxVolumesNormalizer;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;

class AverageMaxVolumesNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AverageMaxVolumesNormalizer::class);
    }

    function it_normalizes_average_and_max_volumes()
    {
        $volumes = new AverageMaxVolumes(10, 6, 14, 'volume_name');
        $this->normalize($volumes)->shouldReturn([
            'volume_name' => [
                'value' => [
                    'average' => 6,
                    'max' => 10,
                ],
                'has_warning' => false,
                'type' => 'average_max'
            ]
        ]);
    }
}
