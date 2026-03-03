<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer\AverageMaxVolumesNormalizer;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer\CountVolumeNormalizer;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer\Volumes;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;

class VolumesSpec extends ObjectBehavior
{
    function let(
        CountVolumeNormalizer $countVolumeNormalizer,
        AverageMaxVolumesNormalizer $averageVolumeNormalizer,
        FeatureFlags $featureFlags,
        CountQuery $countQuery1,
        CountQuery $countQuery2,
        CountQuery $countQuery3,
        AverageMaxQuery $averageMaxQuery
    ) {
        $this->beConstructedWith(
            $countVolumeNormalizer,
            $averageVolumeNormalizer,
            $featureFlags
       );

        $this->addCountVolumeQuery($countQuery1, null);
        $this->addCountVolumeQuery($countQuery2, 'asset_manager');
        $this->addCountVolumeQuery($countQuery2, 'product_rules');
        $this->addAverageMaxVolumeQuery($averageMaxQuery, 'reference_entity');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Volumes::class);
    }

    function it_normalizes_volumes_of_enabled_features(
        $countVolumeNormalizer,
        $averageVolumeNormalizer,
        $countQuery1,
        $countQuery2,
        $averageMaxQuery,
        CountVolume $countVolume1,
        CountVolume $countVolume2,
        AverageMaxVolumes $averageMaxVolumes,
        FeatureFlags $featureFlags
    ) {
        $featureFlags->isEnabled('asset_manager')->willReturn(true);
        $featureFlags->isEnabled('product_rules')->willReturn(false);
        $featureFlags->isEnabled('reference_entity')->willReturn(true);

        $countQuery1->fetch()->willReturn($countVolume1);
        $countQuery2->fetch()->willReturn($countVolume2);
        $averageMaxQuery->fetch()->willReturn($averageMaxVolumes);

        $countVolumeNormalizer->normalize($countVolume1)->willReturn([
            'count_volume_1' => [
                'value' => 10,
                'type' => 'count'
            ]
        ]);

        $countVolumeNormalizer->normalize($countVolume2)->willReturn([
            'count_volume_2' => [
                'value' => 12,
                'type' => 'count'
            ]
        ]);

        $averageVolumeNormalizer->normalize($averageMaxVolumes)->willReturn([
            'average_max_volume' => [
                'value' => ['average' => 4, 'max' => 10],
                'type' => 'average_max'
            ]
        ]);

        $this->volumes()->shouldReturn([
            'count_volume_1' => [
                'value' => 10,
                'type' => 'count'
            ],
            'count_volume_2' => [
                'value' => 12,
                'type' => 'count'
            ],
            'average_max_volume' => [
                'value' => ['average' => 4, 'max' => 10],
                'type' => 'average_max'
            ],
        ]);
    }

}
