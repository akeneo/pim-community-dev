<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Pagination\ParameterValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetMeasurementFamiliesAction
{
    /** @var MeasurementFamilyRepositoryInterface */
    private $measurementFamilyRepository;

    /** @var ParameterValidatorInterface */
    private $parameterValidator;

    /** @var array */
    private $apiConfiguration;

    public function __construct(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        ParameterValidatorInterface $parameterValidator,
        array $apiConfiguration
    ) {
        $this->measurementFamilyRepository = $measurementFamilyRepository;
        $this->parameterValidator = $parameterValidator;
        $this->apiConfiguration = $apiConfiguration;
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->parameterValidator->validate($request->query->all());
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $measurementFamilies = $this->measurementFamilyRepository->all();
        $normalizedMeasurementFamilies = $this->normalizeMeasurementFamilies($measurementFamilies);

        return new JsonResponse($normalizedMeasurementFamilies);
    }

    private function normalizeMeasurementFamilies(array $measurementFamilies): array
    {
        return array_map(
            function (MeasurementFamily $measurementFamily) {
                return $measurementFamily->normalize();
            },
            $measurementFamilies
        );
    }
}
