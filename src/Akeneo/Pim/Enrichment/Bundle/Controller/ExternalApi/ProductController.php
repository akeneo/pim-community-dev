<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\Event\ProductValidationErrorEvent;
use Akeneo\Pim\Enrichment\Bundle\Event\TechnicalErrorEvent;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave\ApiAggregatorForProductPostSaveEventSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\SqlFindProductUuids;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithCompletenessesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsQueryHandler;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ListProductsQueryValidator;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductDomainErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException as ProductInvalidArgumentException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\TwoWayAssociationWithTheSameProductException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownProductException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ExternalApi\PayloadFormat;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\ApiBundle\Cache\WarmupQueryCache;
use Akeneo\Tool\Bundle\ApiBundle\Checker\DuplicateValueChecker;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\ServerErrorResponseException;
use OpenApi\Attributes as OA;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
    private const NO_IDENTIFIER_MESSAGE = 'Validation failed. The identifier field is required for this endpoint. If you want to manipulate products without identifiers, please use products-uuid endpoints.';

    public function __construct(
        protected NormalizerInterface $normalizer,
        protected IdentifiableObjectRepositoryInterface $channelRepository,
        protected AttributeRepositoryInterface $attributeRepository,
        protected IdentifiableObjectRepositoryInterface $productRepository,
        protected PaginatorInterface $offsetPaginator,
        protected PaginatorInterface $searchAfterPaginator,
        protected ValidatorInterface $productValidator,
        protected ProductBuilderInterface $productBuilder,
        protected RemoverInterface $remover,
        protected ObjectUpdaterInterface $updater,
        protected SaverInterface $saver,
        protected UrlGeneratorInterface $router,
        protected FilterInterface $emptyValuesFilter,
        protected StreamResourceResponse $partialUpdateStreamResource,
        protected ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        protected ProductBuilderInterface $variantProductBuilder,
        protected AttributeFilterInterface $productAttributeFilter,
        private AddParent $addParent,
        private ListProductsQueryValidator $listProductsQueryValidator,
        private array $apiConfiguration,
        private ListProductsQueryHandler $listProductsQueryHandler,
        private ConnectorProductNormalizer $connectorProductNormalizer,
        private TokenStorageInterface $tokenStorage,
        private GetConnectorProducts $getConnectorProducts,
        private GetConnectorProducts $getConnectorProductsWithOptions,
        private ApiAggregatorForProductPostSaveEventSubscriber $apiAggregatorForProductPostSave,
        private WarmupQueryCache $warmupQueryCache,
        private EventDispatcherInterface $eventDispatcher,
        protected DuplicateValueChecker $duplicateValueChecker,
        private LoggerInterface $logger,
        private GetProductsWithQualityScoresInterface $getProductsWithQualityScores,
        private RemoveParentInterface $removeParent,
        private GetProductsWithCompletenessesInterface $getProductsWithCompletenesses,
        private SecurityFacade $security,
        private ValidatorInterface $validator,
        private SqlFindProductUuids $findProductUuids
    ) {
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws ServerErrorResponseException
     * @throws UnprocessableEntityHttpException
     */
    #[OA\Get(
        path: '/api/rest/v1/products',
        operationId: 'get_products',
        description: 'This endpoint allows you to get a list of products. Products are paginated and they can be filtered. In the Enterprise Edition, since the 2.0, permissions based on your user groups are applied to the set of products you request.',
        summary: 'Get list of products',
        security: [
            ['bearerToken' => []],
        ],
        tags: ['Product [identifier]'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Filter products, for more details see the <a href="https://api.akeneo.com/documentation/filter.html">Filters</a> section',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string'
                ),
                examples: [
                    'search' => new OA\Examples(
                        example: 'test',
                        summary: 'This filter will return all the products with a sku containing the value "AKN".',
                        description: 'test description',
                        value: '{"sku":[{"operator":"CONTAINS","value":"AKN"}]}',
                        externalValue: 'http://akeneo.com',
                    ),
                ]
            ),
            new OA\Parameter(
                name: 'scope',
                description: 'Filter product values to return scopable attributes for the given channel as well as the non localizable/non scopable attributes, for more details see the <a href="https://api.akeneo.com/documentation/filter.html#via-channel">Filter product values via channel</a> section',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'e-commerce'
                )
            ),
            new OA\Parameter(
                name: 'locales',
                description: 'Filter product values to return localizable attributes for the given locales as well as the non localizable/non scopable attributes, for more details see the <a href="https://api.akeneo.com/documentation/filter.html#via-locale">Filter product values via locale</a> section',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'en_US,fr_FR'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Return products paginated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: '_links',
                            ref: '#/components/schemas/_links',
                        ),
                        new OA\Property(
                            property: 'current_page',
                            description: 'Current page number',
                            type: 'integer',
                            example: 1
                        ),
                        new OA\Property(
                            property: '_embedded',
                            ref: '#/components/schemas/_embedded_product'
                        )
                    ],
                    type: 'object',
                )
            ),
            new OA\Response(
                ref: '#/components/responses/401',
                response: '401'
            ),
            new OA\Response(
                ref: '#/components/responses/403',
                response: '403'
            ),
            new OA\Response(
                ref: '#/components/responses/406',
                response: '406'
            ),
            new OA\Response(
                ref: '#/components/responses/422',
                response: '422'
            ),
        ]
    )]
    public function listAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted('pim_api_product_list');

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
        $query->withAttributeOptions = $request->query->get('with_attribute_options', 'false');
        $query->withQualityScores = $request->query->getAlpha('with_quality_scores', 'false');
        $query->withCompletenesses = $request->query->getAlpha('with_completenesses', 'false');

        try {
            $this->listProductsQueryValidator->validate($query);
            $products = $this->listProductsQueryHandler->handle($query); // in try block as PQB is doing validation also
        } catch (InvalidQueryException $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundHttpException($e->getMessage(), $e);
            }
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (BadRequest400Exception $e) {
            $message = json_decode($e->getMessage(), true);
            if (
                null !== $message && isset($message['error']['root_cause'][0]['type'])
                && 'illegal_argument_exception' === $message['error']['root_cause'][0]['type']
                && 0 === strpos($message['error']['root_cause'][0]['reason'], 'Result window is too large, from + size must be less than or equal to:')
            ) {
                throw new DocumentedHttpException(
                    Documentation::URL_DOCUMENTATION . 'pagination.html#the-search-after-method',
                    'You have reached the maximum number of pages you can retrieve with the "page" pagination type. Please use the search after pagination type instead',
                    $e
                );
            }

            throw new ServerErrorResponseException($e->getMessage(), $e->getCode(), $e);
        }

        return new JsonResponse($this->normalizeProductsList($products, $query));
    }

    public function getAction(Request $request, string $code): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted('pim_api_product_list');

        $connectorProductsQuery = 'true' === $request->query->get('with_attribute_options', "false") ?
            $this->getConnectorProductsWithOptions :
            $this->getConnectorProducts;

        try {
            $user = $this->tokenStorage->getToken()->getUser();
            Assert::isInstanceOf($user, UserInterface::class);

            $uuidsFromIdentifiers = $this->findProductUuids->fromIdentifiers([$code]);
            if (!array_key_exists($code, $uuidsFromIdentifiers)) {
                throw new ObjectNotFoundException();
            }

            $productUuid = $uuidsFromIdentifiers[$code];
            $product = $connectorProductsQuery->fromProductUuid($productUuid, $user->getId());
            $this->eventDispatcher->dispatch(new ReadProductsEvent(1));

            if ($request->query->getAlpha('with_quality_scores', 'false') === 'true') {
                $product = $this->getProductsWithQualityScores->fromConnectorProduct($product);
            }
            if ($request->query->getAlpha('with_completenesses', 'false') === 'true') {
                $product = $this->getProductsWithCompletenesses->fromConnectorProduct($product);
            }
        } catch (ObjectNotFoundException) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist or you do not have permission to access it.', $code));
        }

        $normalizedProduct = $this->connectorProductNormalizer->normalizeConnectorProduct($product);

        return new JsonResponse($normalizedProduct);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function deleteAction(string $code): Response
    {
        $this->denyAccessUnlessAclIsGranted('pim_api_product_remove');

        $product = $this->productRepository->findOneByIdentifier($code);
        if (null === $product) {
            $exception = new UnknownProductException($code);
            $this->eventDispatcher->dispatch(new ProductDomainErrorEvent($exception, null));

            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }

        $this->remover->remove($product);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws BadRequestHttpException
     *
     */
    public function createAction(Request $request): Response
    {
        $this->denyAccessUnlessAclIsGranted('pim_api_product_edit');

        $data = $this->getDecodedContent($request->getContent());

        if (!isset($data['identifier']) || $data['identifier'] === '') {
            throw new DocumentedHttpException(
                Documentation::URL . 'post_products_uuid',
                sprintf(self::NO_IDENTIFIER_MESSAGE)
            );
        }

        $violations = $this->validator->validate($data, new PayloadFormat());
        if (0 < $violations->count()) {
            $firstViolation = $violations->get(0);
            throw new DocumentedHttpException(
                Documentation::URL . 'post_products',
                sprintf('%s Check the expected format on the API documentation.', $firstViolation->getMessage()),
                new \LogicException($firstViolation->getMessage())
            );
        }

        try {
            $this->duplicateValueChecker->check($data);
        } catch (InvalidPropertyTypeException $e) {
            $this->eventDispatcher->dispatch(new TechnicalErrorEvent($e));

            throw new DocumentedHttpException(
                Documentation::URL . 'post_products',
                sprintf('%s Check the expected format on the API documentation.', $e->getMessage()),
                $e
            );
        }

        $data = $this->populateIdentifierProductValue($data);

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
     * @return Response
     * @throws HttpException
     *
     */
    public function partialUpdateAction(Request $request, $code): Response
    {
        $this->denyAccessUnlessAclIsGranted('pim_api_product_edit');

        if (!\is_string($code)) {
            $message = 'The identifier field requires a string.';
            throw new DocumentedHttpException(
                Documentation::URL . 'patch_products__code_',
                sprintf('%s Check the expected format on the API documentation.', $message)
            );
        }

        $data = $this->getDecodedContent($request->getContent());

        if (array_key_exists('identifier', $data) && (null === $data['identifier'] || '' === $data['identifier'])) {
            throw new DocumentedHttpException(
                Documentation::URL . 'patch_products_uuid__uuid_',
                sprintf(self::NO_IDENTIFIER_MESSAGE)
            );
        }

        $violations = $this->validator->validate($data, new PayloadFormat());
        if (0 < $violations->count()) {
            $firstViolation = $violations->get(0);
            throw new DocumentedHttpException(
                Documentation::URL . 'patch_products__code_',
                sprintf('%s Check the expected format on the API documentation.', $firstViolation->getMessage()),
                new \LogicException($firstViolation->getMessage())
            );
        }

        try {
            $this->duplicateValueChecker->check($data);
        } catch (InvalidPropertyTypeException $exception) {
            $this->eventDispatcher->dispatch(new TechnicalErrorEvent($exception));

            throw new DocumentedHttpException(
                Documentation::URL . 'patch_products__code_',
                sprintf('%s Check the expected format on the API documentation.', $exception->getMessage()),
                $exception
            );
        }

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

        $this->updateProduct($product, $data, 'patch_products__code_');
        $this->validateProduct($product);
        $this->saver->save($product);

        $status = $isCreation ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;

        return $this->getResponse($product, $status);
    }

    /**
     * Products are saved 1 by 1, but we batch events in order to improve performances.
     *
     * @param Request $request
     *
     * @return Response
     * @throws HttpException
     *
     */
    public function partialUpdateListAction(Request $request): Response
    {
        $this->denyAccessUnlessAclIsGranted('pim_api_product_edit');

        $this->warmupQueryCache->fromRequest($request);
        $resource = $request->getContent(true);
        $this->apiAggregatorForProductPostSave->activate();

        return $this->partialUpdateStreamResource->streamResponse($resource, [], function () {
            try {
                $this->apiAggregatorForProductPostSave->dispatchAllEvents();
            } catch (\Throwable $exception) {
                $this->logger->warning('An exception has been thrown in the post-save events', [
                    'exception' => $exception,
                ]);
            }
            $this->apiAggregatorForProductPostSave->deactivate();
        });
    }

    /**
     * Get the JSON decoded content. If the content is not a valid JSON, it throws an error 400.
     *
     * @param string $content content of a request to decode
     *
     * @return array
     * @throws BadRequestHttpException
     *
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
     * @param array            $data data of the request already decoded
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
            if ($this->needUpdateFromVariantToSimple($product, $data)) {
                $this->removeParent->from($product);
            }

            if (isset($data['parent']) || $product->isVariant()) {
                $data = $this->productAttributeFilter->filter($data);
            }

            $this->updater->update($product, $data);
        } catch (\Exception $exception) {
            if ($exception instanceof DomainErrorInterface) {
                $this->eventDispatcher->dispatch(new ProductDomainErrorEvent($exception, $product));
            } else {
                $this->eventDispatcher->dispatch(new TechnicalErrorEvent($exception));
            }

            if ($exception instanceof PropertyException) {
                throw new DocumentedHttpException(
                    Documentation::URL . $anchor,
                    sprintf('%s Check the expected format on the API documentation.', $exception->getMessage()),
                    $exception
                );
            }

            if ($exception instanceof TwoWayAssociationWithTheSameProductException) {
                throw new DocumentedHttpException(
                    TwoWayAssociationWithTheSameProductException::TWO_WAY_ASSOCIATIONS_HELP_URL,
                    TwoWayAssociationWithTheSameProductException::TWO_WAY_ASSOCIATIONS_ERROR_MESSAGE,
                    $exception
                );
            }

            if ($exception instanceof InvalidArgumentException || $exception instanceof ProductInvalidArgumentException) {
                throw new AccessDeniedHttpException($exception->getMessage(), $exception);
            }

            throw $exception;
        }
    }

    /**
     * Filter product's values to have only updated or new values.
     *
     * @param ProductInterface $product
     * @param array            $data
     *
     * @return array
     * @throws DocumentedHttpException
     *
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
        } catch (PropertyException $exception) {
            if ($exception instanceof DomainErrorInterface) {
                $this->eventDispatcher->dispatch(new ProductDomainErrorEvent($exception, $product));
            } else {
                $this->eventDispatcher->dispatch(new TechnicalErrorEvent($exception));
            }

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
            $this->eventDispatcher->dispatch(new ProductValidationErrorEvent($violations, $product));

            throw new ViolationHttpException($violations);
        }
    }

    /**
     * Get a response with a location header to the created or updated resource.
     *
     * @param ProductInterface $product
     * @param int              $status
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
            'scope' => null,
            'data' => $identifier,
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

    /**
     * It is a conversion from variant product to simple product if
     * - the product already exists
     * - it is a variant product
     * - and 'parent' is explicitly null
     *
     * @param ProductInterface $product
     * @param array $data
     *
     * @return bool
     */
    protected function needUpdateFromVariantToSimple(ProductInterface $product, array $data): bool
    {
        return null !== $product->getCreated() && $product->isVariant() &&
            array_key_exists('parent', $data) && null === $data['parent'];
    }

    private function normalizeProductsList(ConnectorProductList $connectorProductList, ListProductsQuery $query): array
    {
        $queryParameters = [
            'with_count' => $query->withCount,
            'pagination_type' => $query->paginationType,
            'limit' => $query->limit,
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
        if (true === $query->withAttributeOptionsAsBoolean()) {
            $queryParameters['with_attribute_options'] = 'true';
        }
        if (true === $query->withQualityScores()) {
            $queryParameters['with_quality_scores'] = 'true';
        }

        if (true === $query->withCompletenesses()) {
            $queryParameters['with_completenesses'] = 'true';
        }

        if (PaginationTypes::OFFSET === $query->paginationType) {
            $queryParameters = ['page' => $query->page] + $queryParameters;

            $paginationParameters = [
                'query_parameters' => $queryParameters,
                'list_route_name' => 'pim_api_product_list',
                'item_route_name' => 'pim_api_product_get',
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
                'query_parameters' => $queryParameters,
                'search_after' => [
                    'next' => false !== $lastProduct ? $lastProduct->identifier() : null,
                    'self' => $query->searchAfter,
                ],
                'list_route_name' => 'pim_api_product_list',
                'item_route_name' => 'pim_api_product_get',
                'item_identifier_key' => 'identifier',
            ];

            $paginatedProducts = $this->searchAfterPaginator->paginate(
                $this->connectorProductNormalizer->normalizeConnectorProductList($connectorProductList),
                $parameters,
                null
            );

            return $paginatedProducts;
        }
    }

    private function denyAccessUnlessAclIsGranted(string $acl): void
    {
        if (!$this->security->isGranted($acl)) {
            throw new AccessDeniedHttpException($this->deniedAccessMessage($acl));
        }
    }

    private function deniedAccessMessage(string $acl): string
    {
        switch ($acl) {
            case 'pim_api_product_list':
                return 'Access forbidden. You are not allowed to list products.';
            case 'pim_api_product_edit':
                return 'Access forbidden. You are not allowed to create or update products.';
            case 'pim_api_product_remove':
                return 'Access forbidden. You are not allowed to delete products.';
            default:
                return 'Access forbidden.';
        }
    }
}
