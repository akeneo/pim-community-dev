<?php

namespace AkeneoMeasureBundle\Controller;

use AkeneoMeasureBundle\Infrastructure\Structure\IsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyInterface;
use AkeneoMeasureBundle\Model\MeasurementFamily;
use AkeneoMeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
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

    /** @var IsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyInterface */
    private $isThereAtLeastOneAttributeConfiguredWithMeasurementFamily;

    public function __construct(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        IsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyInterface $isThereAtLeastOneAttributeConfiguredWithMeasurementFamily
    ) {
        $this->measurementFamilyRepository = $measurementFamilyRepository;
        $this->isThereAtLeastOneAttributeConfiguredWithMeasurementFamily = $isThereAtLeastOneAttributeConfiguredWithMeasurementFamily;
    }

    /**
     * @return JsonResponse
     */
    public function indexAction()
    {
        $normalizedMeasurementFamilies = array_map(function (MeasurementFamily $family) {
            $normalizedMeasurementFamily = $family->normalize();
            $normalizedMeasurementFamily['is_locked'] = $this->isThereAtLeastOneAttributeConfiguredWithMeasurementFamily
                ->execute($normalizedMeasurementFamily['code']);

            return $normalizedMeasurementFamily;
        }, $this->measurementFamilyRepository->all());

        return new JsonResponse($normalizedMeasurementFamilies);
    }
}
