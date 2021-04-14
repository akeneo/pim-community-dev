<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Controller;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Controller\VolumeMonitoringController;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer\AverageMaxVolumesNormalizer;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer\CountVolumeNormalizer;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer\Volumes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class VolumeMonitoringControllerSpec extends ObjectBehavior
{
    function let(
        CountVolumeNormalizer $countVolumeNormalizer,
        AverageMaxVolumesNormalizer $averageMaxVolumesNormalizer,
        SecurityFacade $securityFacade
    ) {
        $countVolumeNormalizer->normalize()->willReturn([]);
        $averageMaxVolumesNormalizer->normalize()->willReturn([]);
        $this->beConstructedWith(
            new Volumes(
                $countVolumeNormalizer->getWrappedObject(),
                $averageMaxVolumesNormalizer->getWrappedObject(),
                [],
                []
            ),
            $securityFacade
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(VolumeMonitoringController::class);
    }

    function it_gets_volumes($securityFacade)
    {
        $securityFacade->isGranted('view_catalog_volume_monitoring')->willReturn(true);
        $this->getVolumesAction()->shouldBeLike(new JsonResponse([]));
    }

    public function it_throws_an_exception_if_the_user_is_not_granted_to_view_the_catalog_volume_monitoring($securityFacade)
    {
        $securityFacade->isGranted('view_catalog_volume_monitoring')->willReturn(false);
        $this->shouldThrow(AccessDeniedException::class)->during('getVolumesAction');
    }
}
