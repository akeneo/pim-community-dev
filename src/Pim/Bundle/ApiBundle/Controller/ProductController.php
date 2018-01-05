<?php

declare(strict_types=1);

namespace Pim\Bundle\ApiBundle\Controller;

use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Elasticsearch\Common\Exceptions\ServerErrorResponseException;
use Pim\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface;
use Pim\Bundle\ApiBundle\Documentation;
use Pim\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Pim\Component\Api\Exception\DocumentedHttpException;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Api\Pagination\PaginationTypes;
use Pim\Component\Api\Pagination\PaginatorInterface;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Api\Repository\AttributeRepositoryInterface;
use Pim\Component\Api\Security\PrimaryKeyEncrypter;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Comparator\Filter\FilterInterface;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Exception\UnsupportedFilterException;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\ProductModel\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Query\Sorter\Directions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductController
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $searchAfterPqbFactory;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /** @var PaginatorInterface */
    protected $offsetPaginator;

    /** @var PaginatorInterface */
    protected $searchAfterPaginator;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var  ValidatorInterface */
    protected $productValidator;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var RemoverInterface */
    protected $remover;

    /** @var SaverInterface */
    protected $saver;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var FilterInterface */
    protected $emptyValuesFilter;

    /** @var StreamResourceResponse */
    protected $partialUpdateStreamResource;

    /** @var PrimaryKeyEncrypter */
    protected $primaryKeyEncrypter;

    /** @var array */
    protected $apiConfiguration;

    /** @var QueryParametersCheckerInterface */
    protected $queryParametersChecker;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $fromSizePqbFactory;

    /** @var ProductBuilderInterface */
    protected $variantProductBuilder;

    /**
     * @param ProductQueryBuilderFactoryInterface   $searchAfterPqbFactory
     * @param NormalizerInterface                   $normalizer
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param QueryParametersCheckerInterface       $queryParametersChecker
     * @param AttributeRepositoryInterface          $attributeRepository
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param PaginatorInterface                    $offsetPaginator
     * @param PaginatorInterface                    $searchAfterPaginator
     * @param ParameterValidatorInterface           $parameterValidator
     * @param ValidatorInterface                    $productValidator
     * @param ProductBuilderInterface               $productBuilder
     * @param RemoverInterface                      $remover
     * @param ObjectUpdaterInterface                $updater
     * @param SaverInterface                        $saver
     * @param UrlGeneratorInterface                 $router
     * @param FilterInterface                       $emptyValuesFilter
     * @param StreamResourceResponse                $partialUpdateStreamResource
     * @param PrimaryKeyEncrypter                   $primaryKeyEncrypter
     * @param ProductQueryBuilderFactoryInterface   $fromSizePqbFactory
     * @param ProductBuilderInterface               $variantProductBuilder
     * @param AttributeFilterInterface              $productAttributeFilter
     * @param array                                 $apiConfiguration
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $channelRepository,
        QueryParametersCheckerInterface $queryParametersChecker,
        AttributeRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $productRepository,
        PaginatorInterface $offsetPaginator,
        PaginatorInterface $searchAfterPaginator,
        ParameterValidatorInterface $parameterValidator,
        ValidatorInterface $productValidator,
        ProductBuilderInterface $productBuilder,
        RemoverInterface $remover,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        UrlGeneratorInterface $router,
        FilterInterface $emptyValuesFilter,
        StreamResourceResponse $partialUpdateStreamResource,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductBuilderInterface $variantProductBuilder,
        AttributeFilterInterface $productAttributeFilter,
        array $apiConfiguration
    ) {
        $this->searchAfterPqbFactory = $searchAfterPqbFactory;
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->queryParametersChecker = $queryParametersChecker;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
        $this->offsetPaginator = $offsetPaginator;
        $this->searchAfterPaginator = $searchAfterPaginator;
        $this->parameterValidator = $parameterValidator;
        $this->productValidator = $productValidator;
        $this->productBuilder = $productBuilder;
        $this->remover = $remover;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->router = $router;
        $this->emptyValuesFilter = $emptyValuesFilter;
        $this->partialUpdateStreamResource = $partialUpdateStreamResource;
        $this->primaryKeyEncrypter = $primaryKeyEncrypter;
        $this->fromSizePqbFactory = $fromSizePqbFactory;
        $this->variantProductBuilder = $variantProductBuilder;
        $this->apiConfiguration = $apiConfiguration;
        $this->productAttributeFilter = $productAttributeFilter;
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

        $paginatedProducts = PaginationTypes::OFFSET === $queryParameters['pagination_type'] ?
            $this->searchAfterOffset($request, $channel, $queryParameters, $normalizerOptions) :
            $this->searchAfterIdentifier($request, $channel, $queryParameters, $normalizerOptions);

        return new JsonResponse($paginatedProducts);
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function getAction($code): JsonResponse
    {
        $pqb = $this->fromSizePqbFactory->create(['limit' => 1, 'from' => 0]);
        $pqb->addFilter('identifier', Operators::EQUALS, $code);
        $products = $pqb->execute();

        if (0 === $products->count()) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist.', $code));
        }

        $productApi = $this->normalizer->normalize($products->current(), 'external_api');

        return new JsonResponse($productApi);
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function deleteAction($code): Response
    {
        $product = $this->productRepository->findOneByIdentifier($code);
        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist.', $code));
        }

        $this->remover->remove($product);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     *
     * @throws BadRequestHttpException
     *
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        $data = $this->getDecodedContent($request->getContent());

        $data = $this->populateIdentifierProductValue($data);
        $data = $this->orderData($data);

        if (isset($data['parent'])) {
            $product = $this->variantProductBuilder->createProduct($data['identifier']);
        } else {
            $product = $this->productBuilder->createProduct();
        }

        $this->updateProduct($product, $data, 'post_products');
        $this->validateProduct($product);
        $this->saver->save($product);

        $response = $this->getResponse($product, Response::HTTP_CREATED);

        return $response;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws HttpException
     *
     * @return Response
     */
    public function partialUpdateAction(Request $request, $code): Response
    {
        $data = $this->getDecodedContent($request->getContent());

        $product = $this->productRepository->findOneByIdentifier($code);
        $isCreation = null === $product;

        if ($isCreation) {
            $this->validateCodeConsistency($code, $data);

            if (isset($data['parent'])) {
                $product = $this->variantProductBuilder->createProduct($code);
            } else {
                $product = $this->productBuilder->createProduct($code);
            }
        }

        $data['identifier'] = array_key_exists('identifier', $data) ? $data['identifier'] : $code;
        $data = $this->populateIdentifierProductValue($data);

        if (!$isCreation) {
            $data = $this->filterEmptyValues($product, $data);
        }

        $data = $this->orderData($data);

        $this->updateProduct($product, $data, 'patch_products__code_');
        $this->validateProduct($product);
        $this->saver->save($product);

        $status = $isCreation ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;
        $response = $this->getResponse($product, $status);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @throws HttpException
     *
     * @return Response
     */
    public function partialUpdateListAction(Request $request): Response
    {
        $resource = $request->getContent(true);
        $response = $this->partialUpdateStreamResource->streamResponse($resource);

        return $response;
    }

    /**
     * Get the JSON decoded content. If the content is not a valid JSON, it throws an error 400.
     *
     * @param string $content content of a request to decode
     *
     * @throws BadRequestHttpException
     *
     * @return array
     */
    protected function getDecodedContent($content): array
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    /**
     * Update a product. It throws an error 422 if a problem occurred during the update.
     *
     * @param ProductInterface $product category to update
     * @param array            $data    data of the request already decoded
     * @param string           $anchor
     *
     * @throws DocumentedHttpException
     */
    protected function updateProduct(ProductInterface $product, array $data, string $anchor): void
    {
        if (array_key_exists('variant_group', $data)) {
            throw new DocumentedHttpException(
                Documentation::URL_DOCUMENTATION . 'products-with-variants.html',
                'Property "variant_group" does not exist anymore. Check the link below to understand why.'
            );
        }

        try {
            if (isset($data['parent']) || $product instanceof VariantProductInterface) {
                $data = $this->productAttributeFilter->filter($data);
            }

            $this->updater->update($product, $data);
        } catch (PropertyException $exception) {
            throw new DocumentedHttpException(
                Documentation::URL . $anchor,
                sprintf('%s Check the expected format on the API documentation.', $exception->getMessage()),
                $exception
            );
        } catch (InvalidArgumentException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }
    }

    /**
     * Filter product's values to have only updated or new values.
     *
     * @param ProductInterface $product
     * @param array            $data
     *
     * @throws DocumentedHttpException
     *
     * @return array
     */
    protected function filterEmptyValues(ProductInterface $product, array $data): array
    {
        if (!isset($data['values'])) {
            return $data;
        }

        try {
            $dataFiltered = $this->emptyValuesFilter->filter($product, ['values' => $data['values']]);

            if (!empty($dataFiltered)) {
                $data = array_replace($data, $dataFiltered);
            } else {
                $data['values'] = [];
            }
        } catch (UnknownPropertyException $exception) {
            throw new DocumentedHttpException(
                Documentation::URL . 'patch_products__code_',
                sprintf(
                    'Property "%s" does not exist. Check the expected format on the API documentation.',
                    $exception->getPropertyName()
                ),
                $exception
            );
        }

        return $data;
    }

    /**
     * Validate a product. It throws an error 422 with every violated constraints if
     * the validation failed.
     *
     * @param ProductInterface $product
     *
     * @throws ViolationHttpException
     */
    protected function validateProduct(ProductInterface $product): void
    {
        $violations = $this->productValidator->validate($product, null, ['Default', 'api']);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }

    /**
     * Get a response with a location header to the created or updated resource.
     *
     * @param ProductInterface $product
     * @param int           $status
     *
     * @return Response
     */
    protected function getResponse(ProductInterface $product, int $status): Response
    {
        $response = new Response(null, $status);
        $route = $this->router->generate(
            'pim_api_product_get',
            ['code' => $product->getIdentifier()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $response->headers->set('Location', $route);

        return $response;
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
        ?ChannelInterface $channel
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
                $searchLocale = $request->query->get('search_locale');
                $context['locale'] = isset($filter['locale']) ? $filter['locale'] : $searchLocale;

                if (null !== $context['locale'] && is_string($context['locale'])) {
                    $locales = explode(',', $context['locale']);
                    $this->queryParametersChecker->checkLocalesParameters($locales);
                }

                $context['scope'] = isset($filter['scope']) ? $filter['scope'] : $request->query->get('search_scope');

                if (isset($filter['locales']) && '' !== $filter['locales']) {
                    $context['locales'] = $filter['locales'];

                    $this->queryParametersChecker->checkLocalesParameters(
                        !is_array($context['locales']) ? [$context['locales']] : $context['locales']
                    );
                }

                $value = isset($filter['value']) ? $filter['value'] : null;

                $this->queryParametersChecker->checkPropertyParameters($propertyCode, $filter['operator']);

                $pqb->addFilter($propertyCode, $filter['operator'], $value, $context);
            }
        }
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
     * Add to the data the identifier product value with the same identifier as the value of the identifier property.
     * It silently overwrite the identifier product value if one is already provided in the input.
     *
     * @param array $data
     *
     * @return array
     */
    protected function populateIdentifierProductValue(array $data): array
    {
        $identifierProperty = $this->attributeRepository->getIdentifierCode();
        $identifier = isset($data['identifier']) ? $data['identifier'] : null;

        unset($data['values'][$identifierProperty]);

        $data['values'][$identifierProperty][] = [
            'locale' => null,
            'scope'  => null,
            'data'   => $identifier,
        ];

        return $data;
    }

    /**
     * Throw an exception if the code provided in the url and the identifier provided in the request body
     * are not equals when creating a product with a PATCH method.
     *
     * The identifier in the request body is optional when we create a resource with PATCH.
     *
     * @param string $code code provided in the url
     * @param array  $data body of the request already decoded
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function validateCodeConsistency(string $code, array $data): void
    {
        if (array_key_exists('identifier', $data) && $code !== $data['identifier']) {
            throw new UnprocessableEntityHttpException(
                sprintf(
                    'The identifier "%s" provided in the request body must match the identifier "%s" provided in the url.',
                    $data['identifier'],
                    $code
                )
            );
        }
    }

    /**
     * @param Request               $request
     * @param null|ChannelInterface $channel
     * @param array                 $queryParameters
     * @param array                 $normalizerOptions
     *
     * @return array
     *
     * @throws ServerErrorResponseException
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

        $products = $pqb->execute();

        $paginationParameters = [
            'query_parameters'    => $queryParameters,
            'list_route_name'     => 'pim_api_product_list',
            'item_route_name'     => 'pim_api_product_get',
            'item_identifier_key' => 'identifier',
        ];

        try {
            $count = 'true' === $queryParameters['with_count'] ? $products->count() : null;
            $paginatedProducts = $this->offsetPaginator->paginate(
                $this->normalizer->normalize($products, 'external_api', $normalizerOptions),
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

        return $paginatedProducts;
    }

    /**
     * @param Request               $request
     * @param null|ChannelInterface $channel
     * @param array                 $queryParameters
     * @param array                 $normalizerOptions
     *
     * @throws UnprocessableEntityHttpException
     * @throws DocumentedHttpException
     * @throws ServerErrorResponseException
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
        $productCursor = $pqb->execute();

        $products = [];
        foreach ($productCursor as $product) {
            $products[] = $product;
        }

        $lastProduct = end($products);
        reset($products);

        $parameters = [
            'query_parameters'    => $queryParameters,
            'search_after'        => [
                'next' => false !== $lastProduct ? $this->primaryKeyEncrypter->encrypt($lastProduct->getId()) : null,
                'self' => $searchParameterCrypted,
            ],
            'list_route_name'     => 'pim_api_product_list',
            'item_route_name'     => 'pim_api_product_get',
            'item_identifier_key' => 'identifier'
        ];

        $paginatedProducts = $this->searchAfterPaginator->paginate(
            $this->normalizer->normalize($products, 'external_api', $normalizerOptions),
            $parameters,
            null
        );

        return $paginatedProducts;
    }

    /**
     * This method order the data by setting the parent field first. It comes from the ParentFieldSetter that sets the
     * family from the parent if the product family is null. By doing this the validator does not fail if the family
     * field has been set to null from the API. So to prevent this we order the parent before the family field. this way
     * the field family will be updated to null if the data sent from the API for the family field is null.
     *
     * Example:
     *
     * {
     *     "identifier": "test",
     *     "family": null,
     *     "parent": "amor"
     * }
     *
     * This example does not work because the parent setter will set the family with the parent family.
     *
     * @param array $data
     * @return array
     */
    private function orderData(array $data): array
    {
        if (!isset($data['parent'])) {
            return $data;
        }

        return ['parent' => $data['parent']] + $data;
    }
}
