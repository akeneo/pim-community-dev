<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Pim\Component\Api\Pagination\PaginatorInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Measure families controller
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasureFamiliesController
{
    /** @var array */
    protected $measuresConfig;

    /** @var ArrayConverterInterface */
    protected $measureFamilyConverter;

    /** @var PaginatorInterface */
    protected $paginator;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param ArrayConverterInterface $measureFamilyConverter
     * @param PaginatorInterface      $paginator
     * @param array                   $measures
     * @param array                   $apiConfiguration
     */
    public function __construct(
        ArrayConverterInterface $measureFamilyConverter,
        PaginatorInterface $paginator,
        array $measures,
        array $apiConfiguration
    ) {
        $this->measuresConfig = $measures['measures_config'];
        $this->measureFamilyConverter = $measureFamilyConverter;
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
        if (!array_key_exists($code, $this->measuresConfig)) {
            throw new NotFoundHttpException(sprintf('Measure family with code "%s" does not exist.', $code));
        }

        $normalizedFamily = $this->measureFamilyConverter->convert(
            ['family_code' => $code, 'units' => $this->measuresConfig[$code]]
        );

        return new JsonResponse($normalizedFamily);
    }

    /**
     * @return JsonResponse
     */
    public function listAction()
    {
        $convertedMeasureFamilies= [];
        foreach ($this->measuresConfig as $familyCode => $units) {
            $convertedMeasureFamilies[] = $this->measureFamilyConverter
                ->convert(['family_code' => $familyCode, 'units' =>$units]);
        }

        $defaultParameters = [
            'page'       => 1,
            'limit'      => $this->apiConfiguration['pagination']['limit_by_default'],
            'with_count' => 'false',
        ];

        $parameters = [
            'query_parameters'    => $defaultParameters,
            'list_route_name'     => 'pim_api_measure_families_list',
            'item_route_name'     => 'pim_api_measure_families_get',
        ];

        $paginatedMeasureFamilies = $this->paginator->paginate(
            $convertedMeasureFamilies,
            $parameters,
            null
        );

        return new JsonResponse($paginatedMeasureFamilies['_embedded']);
    }
}
