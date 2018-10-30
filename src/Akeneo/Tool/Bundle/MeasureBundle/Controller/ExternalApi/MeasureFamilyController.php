<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi;

use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Pagination\ParameterValidatorInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasureFamilyController
{
    /** @var array */
    protected $measuresConfig;

    /** @var ArrayConverterInterface */
    protected $measureFamilyConverter;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var PaginatorInterface */
    protected $paginator;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param ArrayConverterInterface     $measureFamilyConverter
     * @param ParameterValidatorInterface $parameterValidator
     * @param PaginatorInterface          $paginator
     * @param array                       $measures
     * @param array                       $apiConfiguration
     */
    public function __construct(
        ArrayConverterInterface $measureFamilyConverter,
        ParameterValidatorInterface $parameterValidator,
        PaginatorInterface $paginator,
        array $measures,
        array $apiConfiguration
    ) {
        $this->measuresConfig = $measures['measures_config'];
        $this->measureFamilyConverter = $measureFamilyConverter;
        $this->parameterValidator = $parameterValidator;
        $this->paginator = $paginator;
        $this->apiConfiguration = $apiConfiguration;
    }

    /**
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function getAction($code)
    {
        $measuresConfig = [];
        foreach ($this->measuresConfig as $key => $value) {
            $measuresConfig[strtolower($key)] = $value;
        }

        if (!array_key_exists(strtolower($code), $measuresConfig)) {
            throw new NotFoundHttpException(sprintf('Measure family with code "%s" does not exist.', $code));
        }

        $normalizedFamily = $this->measureFamilyConverter->convert(
            ['family_code' => $code, 'units' => $measuresConfig[strtolower($code)]]
        );

        return new JsonResponse($normalizedFamily);
    }

    /**
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
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
            'list_route_name'  => 'pim_api_measure_family_list',
            'item_route_name'  => 'pim_api_measure_family_get',
        ];

        $convertedMeasureFamilies = $this->convertMeasureFamilies($queryParameters);

        $count = true === $request->query->getBoolean('with_count') ? count($this->measuresConfig) : null;
        $paginatedMeasureFamilies = $this->paginator->paginate(
            $convertedMeasureFamilies,
            $parameters,
            $count
        );

        return new JsonResponse($paginatedMeasureFamilies);
    }

    /**
     * @return array
     */
    protected function convertMeasureFamilies(array $queryParameters)
    {
        $limit = $queryParameters['limit'];
        $offset = $limit * ($queryParameters['page'] - 1);

        $measureConfig = array_slice($this->measuresConfig, $offset, $queryParameters['limit']);

        $convertedMeasureFamilies= [];
        foreach ($measureConfig as $familyCode => $units) {
            $convertedMeasureFamilies[] = $this->measureFamilyConverter
                ->convert(['family_code' => $familyCode, 'units' => $units]);
        }

        return $convertedMeasureFamilies;
    }
}
