<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\Controller;

use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface;
use Pim\Bundle\ApiBundle\Validator\SearchCriteriasValidator;
use Pim\Component\Api\Pagination\PaginatorInterface;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Api\Repository\AttributeRepositoryInterface;
use Pim\Component\Api\Security\PrimaryKeyEncrypter;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Exception\UnsupportedFilterException;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Query\Sorter\Directions;
use PimEnterprise\Component\Api\Repository\PublishedProductRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PublishedProductController
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $publishedPqbFactory;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var PublishedProductRepositoryInterface */
    protected $publishedProductRepository;

    /** @var PaginatorInterface */
    protected $searchAfterPaginator;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var  PrimaryKeyEncrypter */
    protected $primaryKeyEncrypter;

    /** @var array */
    protected $apiConfiguration;

    /** @var QueryParametersCheckerInterface */
    protected $queryParametersChecker;

    /**
     * @param ProductQueryBuilderFactoryInterface   $publishedPqbFactory
     * @param QueryParametersCheckerInterface       $queryParametersChecker
     * @param NormalizerInterface                   $normalizer
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param AttributeRepositoryInterface          $attributeRepository
     * @param PublishedProductRepositoryInterface   $publishedProductRepository
     * @param PaginatorInterface                    $searchAfterPaginator
     * @param ParameterValidatorInterface           $parameterValidator
     * @param PrimaryKeyEncrypter                   $primaryKeyEncrypter
     * @param array                                 $apiConfiguration
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $publishedPqbFactory,
        QueryParametersCheckerInterface $queryParametersChecker,
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        AttributeRepositoryInterface $attributeRepository,
        PublishedProductRepositoryInterface $publishedProductRepository,
        PaginatorInterface $searchAfterPaginator,
        ParameterValidatorInterface $parameterValidator,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        array $apiConfiguration
    ) {
        $this->publishedPqbFactory = $publishedPqbFactory;
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->publishedProductRepository = $publishedProductRepository;
        $this->searchAfterPaginator = $searchAfterPaginator;
        $this->parameterValidator = $parameterValidator;
        $this->primaryKeyEncrypter = $primaryKeyEncrypter;
        $this->apiConfiguration = $apiConfiguration;
        $this->queryParametersChecker = $queryParametersChecker;
    }

    /**
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return JsonResponse
     */
    public function listAction(Request $request): JsonResponse
    {
        $channel = null;
        if ($request->query->has('scope')) {
            $channel = $this->channelRepository->findOneByIdentifier($request->query->get('scope'));
            if (null === $channel) {
                throw new UnprocessableEntityHttpException(
                    sprintf('Scope "%s" does not exist.', $request->query->get('scope'))
                );
            }
        }

        $normalizerOptions = $this->getNormalizerOptions($request, $channel);

        $queryParameters = array_merge([
            'limit' => $this->apiConfiguration['pagination']['limit_by_default']
        ], $request->query->all());
        $pqbOptions = ['limit' => (int) $queryParameters['limit']];

        $searchParameter = '';
        if (isset($queryParameters['search_after'])) {
            $searchParameter = $queryParameters['search_after'];
        } elseif (isset($queryParameters['search_before']) && '' !== $queryParameters['search_before']) {
            $searchParameter = $queryParameters['search_before'];
        }

        if ('' !== $searchParameter) {
            $searchParameterDecrypted = $this->primaryKeyEncrypter->decrypt($searchParameter);
            $pqbOptions['search_after_unique_key'] = $searchParameterDecrypted;
            $pqbOptions['search_after'] = [$searchParameterDecrypted];
        }

        $pqb = $this->publishedPqbFactory->create($pqbOptions);

        try {
            $this->setPQBFilters($pqb, $request, $channel);
        } catch (
            UnsupportedFilterException
            | PropertyException
            | InvalidOperatorException
            | ObjectNotFoundException
            $e
        ) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $paginatedPublishedProducts = $this->searchAfterIdentifier(
            $pqb,
            $queryParameters,
            $normalizerOptions,
            $searchParameter
        );

        return new JsonResponse($paginatedPublishedProducts);
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function getAction(string $code): JsonResponse
    {
        $publishedProduct = $this->publishedProductRepository->findOneByIdentifier($code);
        if (null === $publishedProduct) {
            throw new NotFoundHttpException(sprintf('Published product "%s" does not exist.', $code));
        }

        $productApi = $this->normalizer->normalize($publishedProduct, 'external_api');

        return new JsonResponse($productApi);
    }

    /**
     * @param Request               $request
     * @param ChannelInterface|null $channel
     *
     * @return array
     */
    protected function getNormalizerOptions(Request $request, ?ChannelInterface $channel): array
    {
        $normalizerOptions = [];

        if ($request->query->has('scope')) {
            $normalizerOptions['channels'] = [$channel->getCode()];
            $normalizerOptions['locales'] = $channel->getLocaleCodes();
        }

        if ($request->query->has('locales')) {
            $locales = explode(',', $request->query->get('locales'));
            $this->queryParametersChecker->checkLocalesParameters($locales, $channel);

            $normalizerOptions['locales'] = explode(',', $request->query->get('locales'));
        }

        if ($request->query->has('attributes')) {
            $attributes = explode(',', $request->query->get('attributes'));
            $this->queryParametersChecker->checkAttributesParameters($attributes);

            $normalizerOptions['attributes'] = explode(',', $request->query->get('attributes'));
        }

        return $normalizerOptions;
    }

    /**
     * Explanation of how links are generated. Take this example with this list of identifiers:
     * A - B - C - D - E - F - G - H
     *
     * With request "&search_after=E&limit=2":
     *  - identifiers returned: F and G
     *  - "next" link to generate: &search_after=G&limit=2 (so the last item returned)
     *  - "previous" link to generate: &search_before=F&limit=2 (so the first item returned)
     *
     * To be able to find items with a "search_before" in request, we reverse the sort of the search:
     * H - G - F - E - D - C - B - A
     *
     * So with a "search_after=F", identifiers returned will be: D and E
     *
     * @param ProductQueryBuilderInterface $pqb
     * @param array                        $queryParameters
     * @param array                        $normalizerOptions
     * @param string                       $searchParameter
     *
     * @return array
     */
    protected function searchAfterIdentifier(
        ProductQueryBuilderInterface $pqb,
        array $queryParameters,
        array $normalizerOptions,
        string $searchParameter
    ): array {
        $direction = isset($queryParameters['search_before']) ? Directions::DESCENDING : Directions::ASCENDING;
        $pqb->addSorter('id', $direction);

        $publishedProductCursor = $pqb->execute();
        $publishedProducts = [];
        foreach ($publishedProductCursor as $publishedProduct) {
            $publishedProducts[] = $publishedProduct;
        }

        if (isset($queryParameters['search_before'])) {
            $publishedProducts = array_reverse($publishedProducts);
        }

        $parameters = [
            'query_parameters'    => $queryParameters,
            'list_route_name'     => 'pimee_api_published_product_list',
            'item_route_name'     => 'pimee_api_published_product_get',
            'item_identifier_key' => 'identifier',
        ];

        $parameters['search_after']['self'] = $searchParameter;
        $parameters['current_page'] = false;
        $parameters['search_after']['next'] = !empty($publishedProducts) ? $this->primaryKeyEncrypter->encrypt(end($publishedProducts)->getId()) : '';
        $parameters['search_after']['previous'] = !empty($publishedProducts) ? $this->primaryKeyEncrypter->encrypt(reset($publishedProducts)->getId()) : '';

        $count = isset($queryParameters['with_count']) && 'true' === $queryParameters['with_count'] ?
            $publishedProductCursor->count() : null;
        $publishedProducts = $this->normalizer->normalize($publishedProducts, 'external_api', $normalizerOptions);

        $paginatedPublishedProducts = $this->searchAfterPaginator->paginate($publishedProducts, $parameters, $count);

        return $paginatedPublishedProducts;
    }

    /**
     * Set the PQB filters.
     * If a scope is requested, add a filter to return only products linked to its category tree
     *
     * @param ProductQueryBuilderInterface $pqb
     * @param Request                      $request
     * @param ChannelInterface|null        $channel
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function setPQBFilters(
        ProductQueryBuilderInterface $pqb,
        Request $request,
        ChannelInterface $channel = null
    ): void {
        $searchParameters = [];

        if ($request->query->has('search')) {
            $searchString = $request->query->get('search', '');
            $searchParameters = $this->queryParametersChecker->checkCriterionParameters($searchString);

            if (isset($searchParameters['categories'])) {
                $this->queryParametersChecker->checkCategoriesParameters($searchParameters['categories']);
            }
        }

        if (null !== $channel && !isset($searchParameters['categories'])) {
            $searchParameters['categories'] = [
                [
                    'operator' => Operators::IN_CHILDREN_LIST,
                    'value'    => [$channel->getCategory()->getCode()]
                ]
            ];
        }

        foreach ($searchParameters as $propertyCode => $filters) {
            foreach ($filters as $filter) {
                $context['locale'] = isset($filter['locale']) ? $filter['locale'] : $request->query->get('search_locale');

                if (null !== $context['locale']) {
                    $locales = explode(',', $context['locale']);
                    $this->queryParametersChecker->checkLocalesParameters($locales);
                }

                $context['scope'] = isset($filter['scope']) ? $filter['scope'] : $request->query->get('search_scope');

                if (isset($filter['locales'])) {
                    $context['locales'] = $filter['locales'];
                    $localeCodes = $context['locales'];
                    if (!is_array($localeCodes)) {
                        $localeCodes = [$context['locales']];
                    }

                    $this->queryParametersChecker->checkLocalesParameters($localeCodes);
                }

                $value = isset($filter['value']) ? $filter['value'] : null;

                $this->queryParametersChecker->checkPropertyParameters($propertyCode, $filter['operator']);

                $pqb->addFilter($propertyCode, $filter['operator'], $value, $context);
            }
        }
    }
}
