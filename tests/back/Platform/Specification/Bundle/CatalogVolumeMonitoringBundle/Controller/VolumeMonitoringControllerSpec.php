<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Controller;

use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Controller\VolumeMonitoringController;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer\AverageMaxVolumesNormalizer;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer\CountVolumeNormalizer;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer\Volumes;
use Symfony\Component\HttpFoundation\JsonResponse;

class VolumeMonitoringControllerSpec extends ObjectBehavior
{
    function let(CountVolumeNormalizer $countVolumeNormalizer, AverageMaxVolumesNormalizer $averageMaxVolumesNormalizer)
    {
        $countVolumeNormalizer->normalize()->willReturn([]);
        $averageMaxVolumesNormalizer->normalize()->willReturn([]);
        $this->beConstructedWith(
            new Volumes(
                $countVolumeNormalizer->getWrappedObject(),
                $averageMaxVolumesNormalizer->getWrappedObject(),
                [],
                []
            )
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(VolumeMonitoringController::class);
    }

    function it_gets_volumes()
    {
        $this->getVolumesAction()->shouldBeLike(new JsonResponse([]));
    }
}
