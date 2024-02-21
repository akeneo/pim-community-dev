<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel;

use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;

class AverageMaxVolumesSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(10, 6, 'volume_name');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AverageMaxVolumes::class);
    }

    function it_has_max_volume()
    {
        $this->getMaxVolume()->shouldReturn(10);
    }

    function it_has_average_volume()
    {
        $this->getAverageVolume()->shouldReturn(6);
    }

    function it_has_volume_name()
    {
        $this->getVolumeName()->shouldReturn('volume_name');
    }
}
