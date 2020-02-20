<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller;

use Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Measures controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasuresController
{
    /** @var LegacyMeasurementProvider */
    private $provider;

    public function __construct(LegacyMeasurementProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return JsonResponse
     */
    public function indexAction()
    {
        return new JsonResponse($this->provider->getMeasurementFamilies());
    }
}
