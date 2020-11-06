<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Controller;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer\Volumes;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VolumeMonitoringController
{
    /** @var Normalizer\Volumes */
    private $volumesNormalizer;

    /**
     * @param Normalizer\Volumes $volumesNormalizer
     */
    public function __construct(Volumes $volumesNormalizer)
    {
        $this->volumesNormalizer = $volumesNormalizer;
    }

    public function getVolumesAction(): JsonResponse
    {
        return new JsonResponse($this->volumesNormalizer->volumes());
    }
}
