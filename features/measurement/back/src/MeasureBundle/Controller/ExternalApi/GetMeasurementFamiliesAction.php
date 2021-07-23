<?php

declare(strict_types=1);

namespace AkeneoMeasureBundle\Controller\ExternalApi;

use AkeneoMeasureBundle\Model\MeasurementFamily;
use AkeneoMeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetMeasurementFamiliesAction
{
    /** @var MeasurementFamilyRepositoryInterface */
    private $measurementFamilyRepository;

    public function __construct(MeasurementFamilyRepositoryInterface $measurementFamilyRepository)
    {
        $this->measurementFamilyRepository = $measurementFamilyRepository;
    }

    public function __invoke(): JsonResponse
    {
        $measurementFamilies = $this->measurementFamilyRepository->all();
        $normalizedMeasurementFamilies = array_map(function (MeasurementFamily $measurementFamily) {
            return $measurementFamily->normalizeWithIndexedUnits();
        }, $measurementFamilies);

        return new JsonResponse($normalizedMeasurementFamilies);
    }
}
