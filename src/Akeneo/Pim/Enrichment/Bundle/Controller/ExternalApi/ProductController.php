<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave\ApiAggregatorForProductPostSaveEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsQueryHandler;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ListProductsQueryValidator;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\ApiBundle\Cache\WarmupQueryCache;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Security\PrimaryKeyEncrypter;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\ServerErrorResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductController
{
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

    /** @var ProductQueryBuilderFactoryInterface */
    protected $fromSizePqbFactory;

    /** @var ProductBuilderInterface */
    protected $variantProductBuilder;

    /** @var AddParent */
    protected $addParent;

    /** @var AttributeFilterInterface */
    protected $productAttributeFilter;

    /** @var ListProductsQueryValidator */
    private $listProductsQueryValidator;

    /** @var ListProductsQueryHandler */
    private $listProductsQueryHandler;

    /** @var ConnectorProductNormalizer */
    private $connectorProductNormalizer;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var GetConnectorProducts */
    private $getConnectorProducts;

    /** @var ApiAggregatorForProductPostSaveEventSubscriber */
    private $apiAggregatorForProductPostSave;

    /** @var WarmupQueryCache */
    private $warmupQueryCache;

    public function __construct(
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $productRepository,
        PaginatorInterface $offsetPaginator,
        PaginatorInterface $searchAfterPaginator,
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
        AddParent $addParent,
        ListProductsQueryValidator $listProductsQueryValidator,
        array $apiConfiguration,
        ListProductsQueryHandler $listProductsQueryHandler,
        ConnectorProductNormalizer $connectorProductNormalizer,
        TokenStorageInterface $tokenStorage,
        GetConnectorProducts $getConnectorProducts,
        ApiAggregatorForProductPostSaveEventSubscriber $apiAggregatorForProductPostSave,
        WarmupQueryCache $warmupQueryCache
    ) {
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
        $this->offsetPaginator = $offsetPaginator;
        $this->searchAfterPaginator = $searchAfterPaginator;
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
        $this->addParent = $addParent;
        $this->listProductsQueryValidator = $listProductsQueryValidator;
        $this->listProductsQueryHandler = $listProductsQueryHandler;
        $this->connectorProductNormalizer = $connectorProductNormalizer;
        $this->tokenStorage = $tokenStorage;
        $this->getConnectorProducts = $getConnectorProducts;
        $this->apiAggregatorForProductPostSave = $apiAggregatorForProductPostSave;
        $this->warmupQueryCache = $warmupQueryCache;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws ServerErrorResponseException
     * @throws UnprocessableEntityHttpException
     */
    public function listAction(Request $request): JsonResponse
    {
        $query = new ListProductsQuery();

        if ($request->query->has('attributes')) {
            $query->attributeCodes = explode(',', $request->query->get('attributes'));
        }
        if ($request->query->has('locales')) {
            $query->localeCodes = explode(',', $request->query->get('locales'));
        }
        if ($request->query->has('search')) {
            $query->search = json_decode($request->query->get('search'), true);
            if (!is_array($query->search)) {
                throw new BadRequestHttpException('Search query parameter should be valid JSON.');
            }
        }

        $user = $this->tokenStorage->getToken()->getUser();
        Assert::isInstanceOf($user, UserInterface::class);

        $query->channelCode = $request->query->get('scope', null);
        $query->limit = $request->query->get('limit', $this->apiConfiguration['pagination']['limit_by_default']);
        $query->paginationType = $request->query->get('pagination_type', PaginationTypes::OFFSET);
        $query->searchLocaleCode = $request->query->get('search_locale', null);
        $query->withCount = $request->query->get('with_count', 'false');
        $query->page = $request->query->get('page', 1);
        $query->searchChannelCode = $request->query->get('search_scope', null);
        $query->searchAfter = $request->query->get('search_after', null);
        $query->userId = $user->getId();

        try {
            $this->listProductsQueryValidator->validate($query);
            $products = $this->listProductsQueryHandler->handle($query); // in try block as PQB is doing validation also
        } catch (InvalidQueryException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (BadRequest400Exception $e) {
            $message = json_decode($e->getMessage(), true);
            if (null !== $message && isset($message['error']['root_cause'][0]['type'])
                && 'illegal_argument_exception' === $message['error']['root_cause'][0]['type']
                && 0 === strpos($message['error']['root_cause'][0]['reason'], 'Result window is too large, from + size must be less than or equal to:')) {
                throw new DocumentedHttpException(
                    Documentation::URL_DOCUMENTATION . 'pagination.html#search-after-type',
                    'You have reached the maximum number of pages you can retrieve with the "page" pagination type. Please use the search after pagination type instead',
                    $e
                );
            }

            throw new ServerErrorResponseException($e->getMessage(), $e->getCode(), $e);
        }

        return new JsonResponse($this->normalizeProductsList($products, $query));
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getAction(string $code): JsonResponse
    {
        try {
            $user = $this->tokenStorage->getToken()->getUser();
            Assert::isInstanceOf($user, UserInterface::class);

            $product = $this->getConnectorProducts->fromProductIdentifier($code, $user->getId());
        } catch (ObjectNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist.', $code));
        }

        $normalizedProduct = $this->connectorProductNormalizer->normalizeConnectorProduct($product);

        return new JsonResponse($normalizedProduct);
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
        if ($this->needUpdateFromProductToVariant($product, $data, $isCreation)) {
            try {
                $product = $this->addParent->to($product, (string) $data['parent']);
            } catch (\InvalidArgumentException $exception) {
                throw new UnprocessableEntityHttpException($exception->getMessage());
            }
            $isCreation = true;
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
     * Products are saved 1 by 1, but we batch events in order to improve performances.
     *
     * @param Request $request
     * @return Response
     * @throws HttpException
     */
    public function partialUpdateListAction(Request $request): Response
    {
        $this->warmupQueryCache->fromRequest($request);
        $resource = $request->getContent(true);
        $this->apiAggregatorForProductPostSave->activate();
        $response = $this->partialUpdateStreamResource->streamResponse($resource, [], function () {
            $this->apiAggregatorForProductPostSave->dispatchAllEvents();
            $this->apiAggregatorForProductPostSave->deactivate();
        });

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
            if (isset($data['parent']) || $product->isVariant()) {
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
        } catch (PropertyException $exception) {
            throw new DocumentedHttpException(
                Documentation::URL . 'patch_products__code_',
                sprintf('%s Check the expected format on the API documentation.', $exception->getMessage()),
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

    protected function getNormalizerOptions(ListProductsQuery $query): array
    {
        $normalizerOptions = [];

        if (null !== $query->channelCode) {
            $channel = $this->channelRepository->findOneByIdentifier($query->channelCode);

            $normalizerOptions['channels'] = [$channel->getCode()];
            $normalizerOptions['locales'] = $channel->getLocaleCodes();
        }

        if (null !== $query->localeCodes) {
            $normalizerOptions['locales'] = $query->localeCodes;
        }

        if (null !== $query->attributeCodes) {
            $normalizerOptions['attributes'] = $query->attributeCodes;
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

    /**
     * Is it an update from a product to a variant product ?
     * That's the case if we are updating (and not creating) a product (not a variant) and 'parent' index is in $data.
     *
     * @param ProductInterface $product
     * @param array            $data
     * @param bool             $isCreation
     *
     * @return bool
     */
    protected function needUpdateFromProductToVariant(ProductInterface $product, array $data, bool $isCreation): bool
    {
        return !$isCreation && !$product->isVariant() &&
            isset($data['parent']) && '' !== $data['parent'];
    }

    private function normalizeProductsList(ConnectorProductList $connectorProductList, ListProductsQuery $query): array
    {
        $queryParameters = [
            'with_count' => $query->withCount,
            'pagination_type' => $query->paginationType,
            'limit' => $query->limit
        ];

        if ($query->search !== []) {
            $queryParameters['search'] = json_encode($query->search);
        }
        if (null !== $query->channelCode) {
            $queryParameters['scope'] = $query->channelCode;
        }
        if (null !== $query->searchChannelCode) {
            $queryParameters['search_scope'] = $query->searchChannelCode;
        }
        if (null !== $query->localeCodes) {
            $queryParameters['locales'] = join(',', $query->localeCodes);
        }
        if (null !== $query->attributeCodes) {
            $queryParameters['attributes'] = join(',', $query->attributeCodes);
        }

        if (PaginationTypes::OFFSET === $query->paginationType) {
            $queryParameters = ['page' => $query->page] + $queryParameters;

            $paginationParameters = [
                'query_parameters'    => $queryParameters,
                'list_route_name'     => 'pim_api_product_list',
                'item_route_name'     => 'pim_api_product_get',
                'item_identifier_key' => 'identifier',
            ];

            $count = $query->withCountAsBoolean() ? $connectorProductList->totalNumberOfProducts() : null;
            $paginatedProducts = $this->offsetPaginator->paginate(
                $this->connectorProductNormalizer->normalizeConnectorProductList($connectorProductList),
                $paginationParameters,
                $count
            );

            return $paginatedProducts;
        } else {
            $connectorProducts = $connectorProductList->connectorProducts();
            $lastProduct = end($connectorProducts);

            $parameters = [
                'query_parameters'    => $queryParameters,
                'search_after'        => [
                    'next' => false !== $lastProduct ? $this->primaryKeyEncrypter->encrypt($lastProduct->id()) : null,
                    'self' => $query->searchAfter,
                ],
                'list_route_name'     => 'pim_api_product_list',
                'item_route_name'     => 'pim_api_product_get',
                'item_identifier_key' => 'identifier'
            ];

            $paginatedProducts = $this->searchAfterPaginator->paginate(
                $this->connectorProductNormalizer->normalizeConnectorProductList($connectorProductList),
                $parameters,
                null
            );

            return $paginatedProducts;
        }
    }
}
