<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel;

use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;

class CountVolumeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(10, 'volume_name');
    }
    function it_is_initializable()
    {
        $this->shouldHaveType(CountVolume::class);
    }

    function it_has_volume()
    {
        $this->getVolume()->shouldReturn(10);
    }

    function it_has_volume_name()
    {
        $this->getVolumeName()->shouldReturn('volume_name');
    }
}
