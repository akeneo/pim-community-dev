<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Pagination\HalPaginator;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
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
    /** @var LocaleRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var HalPaginator */
    protected $paginator;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var string[] */
    protected $authorizedFieldFilters = ['enabled'];

    /**
     * @param LocaleRepositoryInterface  $repository
     * @param NormalizerInterface         $normalizer
     * @param HalPaginator                $paginator
     * @param ParameterValidatorInterface $parameterValidator
     */
    public function __construct(
        LocaleRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        HalPaginator $paginator,
        ParameterValidatorInterface $parameterValidator
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
        $this->paginator = $paginator;
        $this->parameterValidator = $parameterValidator;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
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
     */
    public function listAction(Request $request)
    {
        $criterias = $this->prepareSearchCriterias($request);

        $queryParameters = [];
        $queryParameters['page'] = $request->query->get('page', 1);
        $queryParameters['limit'] = $request->query->get('limit', 10);

        try {
            $this->parameterValidator->validate($queryParameters);
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }
        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);

        // TODO: count and full hydration for counting is temporary, will be done with API-114
        $locales = $this->repository->findBy($criterias, ['code' => 'ASC']);
        $count = count($locales);
        $locales = array_slice($locales, $offset, $queryParameters['limit']);

        $localesApi = $this->normalizer->normalize($locales, 'external_api');

        $paginatedLocales = $this->paginator->paginate(
            $localesApi,
            array_merge($request->query->all(), $queryParameters),
            $count,
            'pim_api_rest_locale_list',
            'pim_api_rest_locale_get',
            'code'
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
    protected function prepareSearchCriterias(Request $request)
    {
        $criterias = [];
        if (false === $request->query->has('search')) {
            return $criterias;
        }
        $searchString = $request->query->get('search', '');
        $searchParameters = json_decode($searchString, true);

        if (null === $searchParameters) {
            throw new BadRequestHttpException('Search query parameter should be valid JSON.');
        }
        foreach ($searchParameters as $searchKey => $searchParameter) {
            if (0 === count($searchParameter)) {
                throw new UnprocessableEntityHttpException(
                    sprintf('Operator and value are missing for the property "%s".', $searchKey)
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

                if (!in_array($searchKey, $this->authorizedFieldFilters) || '=' !== $searchOperator['operator']) {
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
                            'Filter "%s" with operator "%s" expects a boolean value',
                            $searchKey,
                            $searchOperator['operator']
                        )
                    );
                }

                $criterias['activated'] = $searchOperator['value'];
            }
        }

        return $criterias;
    }
}
