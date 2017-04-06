<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Pagination\PaginatorInterface;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class LocaleController
{
    /** @var ApiResourceRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var PaginatorInterface */
    protected $paginator;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var array */
    protected $apiConfiguration;

    /** @var string[] */
    protected $authorizedFieldFilters = ['enabled'];

    /**
     * @param ApiResourceRepositoryInterface $repository
     * @param NormalizerInterface            $normalizer
     * @param PaginatorInterface             $paginator
     * @param ParameterValidatorInterface    $parameterValidator
     * @param array                          $apiConfiguration
     */
    public function __construct(
        ApiResourceRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        PaginatorInterface $paginator,
        ParameterValidatorInterface $parameterValidator,
        array $apiConfiguration
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
        $this->paginator = $paginator;
        $this->parameterValidator = $parameterValidator;
        $this->apiConfiguration = $apiConfiguration;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_api_locale_list")
     */
    public function getAction(Request $request, $code)
    {
        $locale = $this->repository->findOneByIdentifier($code);
        if (null === $locale) {
            throw new NotFoundHttpException(sprintf('Locale "%s" does not exist.', $code));
        }

        $localeApi = $this->normalizer->normalize($locale, 'external_api');

        return new JsonResponse($localeApi);
    }

    /**
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     * @return JsonResponse
     *
     * @AclAncestor("pim_api_locale_list")
     */
    public function listAction(Request $request)
    {
        $searchCriterias = $this->validateSearchCriterias($request);
        $criterias = $this->prepareSearchCriterias($searchCriterias);

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

        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);
        $locales = $this->repository->searchAfterOffset(
            $criterias,
            ['code' => 'ASC'],
            $queryParameters['limit'],
            $offset
        );

        $parameters = [
            'query_parameters' => $queryParameters,
            'list_route_name'  => 'pim_api_locale_list',
            'item_route_name'  => 'pim_api_locale_get',
        ];

        $count = true === $request->query->getBoolean('with_count') ? $this->repository->count($criterias) : null;
        $paginatedLocales = $this->paginator->paginate(
            $this->normalizer->normalize($locales, 'external_api'),
            $parameters,
            $count
        );

        return new JsonResponse($paginatedLocales);
    }

    /**
     * Prepares criterias from search parameters
     * It throws exceptions if search parameters are not correctly filled
     * Only activated = filter is authorized today
     *
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     * @throws BadRequestHttpException
     * @return array
     */
    protected function validateSearchCriterias(Request $request)
    {
        if (!$request->query->has('search')) {
            return [];
        }

        $searchString = $request->query->get('search', '');
        $searchParameters = json_decode($searchString, true);

        if (null === $searchParameters) {
            throw new BadRequestHttpException('Search query parameter should be valid JSON.');
        }
        if (!is_array($searchParameters)) {
            throw new UnprocessableEntityHttpException(
                sprintf('Search query parameter has to be an array, "%s" given.', gettype($searchParameters))
            );
        }
        foreach ($searchParameters as $searchKey => $searchParameter) {
            if (!is_array($searchParameters) || !isset($searchParameter[0])) {
                throw new UnprocessableEntityHttpException(
                    sprintf(
                        'Structure of filter "%s" should respect this structure: %s.',
                        $searchKey,
                        sprintf('{"%s":[{"operator": "my_operator", "value": "my_value"}]}', $searchKey)
                    )
                );
            }

            foreach ($searchParameter as $searchOperator) {
                if (!isset($searchOperator['operator'])) {
                    throw new UnprocessableEntityHttpException(
                        sprintf('Operator is missing for the property "%s".', $searchKey)
                    );
                }
                if (!isset($searchOperator['value'])) {
                    throw new UnprocessableEntityHttpException(
                        sprintf('Value is missing for the property "%s".', $searchKey)
                    );
                }

                if (!in_array($searchKey, $this->authorizedFieldFilters)
                    || Operators::EQUALS !== $searchOperator['operator']) {
                    throw new UnprocessableEntityHttpException(
                        sprintf(
                            'Filter on property "%s" is not supported or does not support operator "%s".',
                            $searchKey,
                            $searchOperator['operator']
                        )
                    );
                }
                if (!is_bool($searchOperator['value'])) {
                    throw new UnprocessableEntityHttpException(
                        sprintf(
                            'Filter "%s" with operator "%s" expects a boolean value.',
                            $searchKey,
                            $searchOperator['operator']
                        )
                    );
                }
            }
        }

        return $searchParameters;
    }

    /**
     * Prepares search criterias
     * For now, only enabled filter with operator "=" are managed
     * Value is a boolean
     *
     * @param array $searchParameters
     *
     * @return array
     */
    protected function prepareSearchCriterias(array $searchParameters)
    {
        if (empty($searchParameters)) {
            return [];
        }

        return [
            'activated' => $searchParameters['enabled'][0]['value']
        ];
    }
}
