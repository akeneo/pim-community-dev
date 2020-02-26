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

    /** @var PaginatorInterface */
    private $paginator;

    /** @var array */
    private $apiConfiguration;

    public function __construct(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        ParameterValidatorInterface $parameterValidator,
        PaginatorInterface $paginator,
        array $apiConfiguration
    ) {
        $this->measurementFamilyRepository = $measurementFamilyRepository;
        $this->parameterValidator = $parameterValidator;
        $this->paginator = $paginator;
        $this->apiConfiguration = $apiConfiguration;
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->parameterValidator->validate($request->query->all());
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $defaultParameters = [
            'page'       => 1,
            'limit'      => $this->apiConfiguration['pagination']['limit_by_default'],
            'with_count' => 'false',
        ];

        $queryParameters = array_merge($defaultParameters, $request->query->all());
        $parameters = [
            'query_parameters' => $queryParameters,
            'list_route_name'  => 'pim_api_measurement_family_get',
        ];

        $measurementFamilies = $this->measurementFamilyRepository->all();
        $normalizedMeasurementFamilies = $this->normalizeMeasurementFamilies($measurementFamilies, $queryParameters);
        $count = true === $request->query->getBoolean('with_count') ? count($measurementFamilies) : null;
        $paginatedMeasureFamilies = $this->paginator->paginate($normalizedMeasurementFamilies, $parameters, $count);

        return new JsonResponse($paginatedMeasureFamilies);
    }

    private function normalizeMeasurementFamilies(array $measurementFamilies, array $queryParameters): array
    {
        $limit = $queryParameters['limit'];
        $offset = $limit * ($queryParameters['page'] - 1);

        $measurementFamiliesPage = array_slice($measurementFamilies, $offset, $queryParameters['limit']);

        return array_map(
            function (MeasurementFamily $measurementFamily) {
                return $measurementFamily->normalize();
            },
            $measurementFamiliesPage
        );
    }
}
