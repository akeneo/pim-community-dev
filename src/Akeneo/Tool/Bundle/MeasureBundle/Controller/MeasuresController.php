<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
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
    /** @var MeasurementFamilyRepositoryInterface */
    private $measurementFamilyRepository;

    public function __construct(MeasurementFamilyRepositoryInterface $measurementFamilyRepository)
    {
        $this->measurementFamilyRepository = $measurementFamilyRepository;
    }

    /**
     * @return JsonResponse
     */
    public function indexAction()
    {
        $normalizedMeasurementFamilies = array_map(function (MeasurementFamily $family) {
            return $family->normalize();
        }, $this->measurementFamilyRepository->all());

        return new JsonResponse($normalizedMeasurementFamilies);
    }
}
