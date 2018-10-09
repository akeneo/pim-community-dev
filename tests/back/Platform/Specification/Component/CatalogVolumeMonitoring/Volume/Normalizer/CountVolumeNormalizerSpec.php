<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer;

use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer\CountVolumeNormalizer;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;

class CountVolumeNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CountVolumeNormalizer::class);
    }

    function it_normalizes_count_volume()
    {
        $volumes = new CountVolume(10, 8, 'volume_name');
        $this->normalize($volumes)->shouldReturn([
            'volume_name' => [
                'value' => 10,
                'has_warning' => true,
                'type' => 'count'
            ]
        ]);
    }
}
