<?php

declare(strict_types=1);

namespace spec\Pim\Component\CatalogVolumeMonitoring\Volume\Service;

use PhpSpec\ObjectBehavior;
use Pim\Component\CatalogVolumeMonitoring\Volume\Model\AggregatedVolume;
use Pim\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Pim\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Pim\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Pim\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Pim\Component\CatalogVolumeMonitoring\Volume\Repository\AggregatedVolumeRepositoryInterface;
use Pim\Component\CatalogVolumeMonitoring\Volume\Service\VolumeAggregation;
use Prophecy\Argument;

class VolumeAggregationSpec extends ObjectBehavior
{
    function let(
        AggregatedVolumeRepositoryInterface $aggregatedVolumeRepository,
        CountQuery $countQuery1,
        CountQuery $countQuery2,
        AverageMaxQuery $averageMaxQuery
    ) {
        $this->beConstructedWith(
            $aggregatedVolumeRepository,
            [$countQuery1, $countQuery2],
            [$averageMaxQuery]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(VolumeAggregation::class);
    }

    function it_aggregates_volumes(
        $aggregatedVolumeRepository,
        $countQuery1,
        $countQuery2,
        $averageMaxQuery
    ) {
        $countQuery1->fetch()->willReturn(new CountVolume(11, 20, 'count_volume_1'));
        $countQuery2->fetch()->willReturn(new CountVolume(7, 10, 'count_volume_2'));
        $averageMaxQuery->fetch()->willReturn(new AverageMaxVolumes(42, 34, -1, 'average_max_volume'));

        $aggregatedVolumeRepository->add(Argument::that(function (AggregatedVolume $aggregatedVolume) {
            return 'count_volume_1' === $aggregatedVolume->getVolumeName()
                && ['value' => 11] === $aggregatedVolume->getVolume();
        }))->shouldBeCalled();

        $aggregatedVolumeRepository->add(Argument::that(function (AggregatedVolume $aggregatedVolume) {
            return 'count_volume_2' === $aggregatedVolume->getVolumeName()
                && ['value' => 7] === $aggregatedVolume->getVolume();
        }))->shouldBeCalled();

        $aggregatedVolumeRepository->add(Argument::that(function (AggregatedVolume $aggregatedVolume) {
            return 'average_max_volume' === $aggregatedVolume->getVolumeName()
                && ['value' => ['max' => 42, 'average' => 34]] === $aggregatedVolume->getVolume();
        }))->shouldBeCalled();

        $this->aggregate();
    }
}
