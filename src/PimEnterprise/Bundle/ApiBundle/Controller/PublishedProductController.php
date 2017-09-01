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
use Elasticsearch\Common\Exceptions\ServerErrorResponseException;
use Pim\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface;
use Pim\Bundle\ApiBundle\Documentation;
use Pim\Component\Api\Exception\DocumentedHttpException;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Pagination\PaginationTypes;
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
    protected $searchAfterPqbFactory;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $fromSizePqbFactory;

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

    /** @var PaginatorInterface */
    protected $offsetPaginator;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var  PrimaryKeyEncrypter */
    protected $primaryKeyEncrypter;

    /** @var array */
    protected $apiConfiguration;

    /** @var QueryParametersCheckerInterface */
    protected $queryParametersChecker;

    /**
     * @param ProductQueryBuilderFactoryInterface   $searchAfterPqbFactory
     * @param ProductQueryBuilderFactoryInterface   $fromSizePqbFactory
     * @param QueryParametersCheckerInterface       $queryParametersChecker
     * @param NormalizerInterface                   $normalizer
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param AttributeRepositoryInterface          $attributeRepository
     * @param PublishedProductRepositoryInterface   $publishedProductRepository
     * @param PaginatorInterface                    $searchAfterPaginator
     * @param PaginatorInterface                    $offsetPaginator
     * @param ParameterValidatorInterface           $parameterValidator
     * @param PrimaryKeyEncrypter                   $primaryKeyEncrypter
     * @param array                                 $apiConfiguration
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        QueryParametersCheckerInterface $queryParametersChecker,
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        AttributeRepositoryInterface $attributeRepository,
        PublishedProductRepositoryInterface $publishedProductRepository,
        PaginatorInterface $searchAfterPaginator,
        PaginatorInterface $offsetPaginator,
        ParameterValidatorInterface $parameterValidator,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        array $apiConfiguration
    ) {
        $this->searchAfterPqbFactory = $searchAfterPqbFactory;
        $this->fromSizePqbFactory = $fromSizePqbFactory;
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->publishedProductRepository = $publishedProductRepository;
        $this->searchAfterPaginator = $searchAfterPaginator;
        $this->offsetPaginator = $offsetPaginator;
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
        try {
            $this->parameterValidator->validate($request->query->all(), ['support_search_after' => true]);
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

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

        $defaultParameters = [
            'pagination_type' => PaginationTypes::OFFSET,
            'limit'           => $this->apiConfiguration['pagination']['limit_by_default'],
        ];

        $queryParameters = array_merge($defaultParameters, $request->query->all());

        $paginatedPublishedProducts = PaginationTypes::OFFSET === $queryParameters['pagination_type'] ?
            $this->searchAfterOffset($request, $channel, $queryParameters, $normalizerOptions) :
            $this->searchAfterIdentifier($request, $channel, $queryParameters, $normalizerOptions);

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
     * @param Request               $request
     * @param null|ChannelInterface $channel
     * @param array                 $queryParameters
     * @param array                 $normalizerOptions
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return array
     */
    protected function searchAfterOffset(
        Request $request,
        ?ChannelInterface $channel,
        array $queryParameters,
        array $normalizerOptions
    ): array {
        $from = isset($queryParameters['page']) ? ($queryParameters['page'] - 1) * $queryParameters['limit'] : 0;
        $pqb = $this->fromSizePqbFactory->create(['limit' => (int) $queryParameters['limit'], 'from' => (int) $from]);

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

        $queryParameters = array_merge(['page' => 1, 'with_count' => 'false'], $queryParameters);
        $pqb->addSorter('id', Directions::ASCENDING);

        $publishedProducts = $pqb->execute();

        $paginationParameters = [
            'query_parameters'    => $queryParameters,
            'list_route_name'     => 'pimee_api_published_product_list',
            'item_route_name'     => 'pimee_api_published_product_get',
            'item_identifier_key' => 'identifier',
        ];

        try {
            $count = 'true' === $queryParameters['with_count'] ? $publishedProducts->count() : null;
            $paginatedPublishedProducts = $this->offsetPaginator->paginate(
                $this->normalizer->normalize($publishedProducts, 'external_api', $normalizerOptions),
                $paginationParameters,
                $count
            );
        } catch (ServerErrorResponseException $e) {
            $message = json_decode($e->getMessage(), true);
            if (null !== $message && isset($message['error']['root_cause'][0]['type'])
                && 'query_phase_execution_exception' === $message['error']['root_cause'][0]['type']) {
                throw new DocumentedHttpException(
                    Documentation::URL_DOCUMENTATION . 'pagination.html#search-after-type',
                    'You have reached the maximum number of pages you can retrieve with the "page" pagination type. Please use the search after pagination type instead',
                    $e
                );
            }

            throw new ServerErrorResponseException($e->getMessage(), $e->getCode(), $e);
        }

        return $paginatedPublishedProducts;
    }

    /**
     * @param Request               $request
     * @param null|ChannelInterface $channel
     * @param array                 $queryParameters
     * @param array                 $normalizerOptions
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return array
     */
    protected function searchAfterIdentifier(
        Request $request,
        ?ChannelInterface $channel,
        array $queryParameters,
        array $normalizerOptions
    ): array {
        $pqbOptions = ['limit' => (int) $queryParameters['limit']];
        $searchParameterCrypted = null;
        if (isset($queryParameters['search_after'])) {
            $searchParameterCrypted = $queryParameters['search_after'];
            $searchParameterDecrypted = $this->primaryKeyEncrypter->decrypt($queryParameters['search_after']);
            $pqbOptions['search_after_unique_key'] = $searchParameterDecrypted;
            $pqbOptions['search_after'] = [$searchParameterDecrypted];
        }
        $pqb = $this->searchAfterPqbFactory->create($pqbOptions);

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

        $pqb->addSorter('id', Directions::ASCENDING);
        $publishedProductCursor = $pqb->execute();

        $publishedProducts = [];
        foreach ($publishedProductCursor as $publishedProduct) {
            $publishedProducts[] = $publishedProduct;
        }

        $lastPublishedProduct = end($publishedProducts);

        reset($publishedProducts);

        $parameters = [
            'query_parameters'    => $queryParameters,
            'search_after'        => [
                'next' => false !== $lastPublishedProduct ? $this->primaryKeyEncrypter->encrypt($lastPublishedProduct->getId()) : null,
                'self' => $searchParameterCrypted,
            ],
            'list_route_name'     => 'pimee_api_published_product_list',
            'item_route_name'     => 'pimee_api_published_product_get',
            'item_identifier_key' => 'identifier'
        ];

        $paginatedPublishedProducts = $this->searchAfterPaginator->paginate(
            $this->normalizer->normalize($publishedProducts, 'external_api', $normalizerOptions),
            $parameters,
            null
        );

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
