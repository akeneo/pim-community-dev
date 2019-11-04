<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave\ApiAggregatorForProductModelPostSaveEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQueryHandler;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ListProductModelsQueryValidator;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
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
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
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
 * @author    Willy MESNAGE <willy.mesnage@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelController
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbSearchAfterFactory;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /** @var PaginatorInterface */
    protected $offsetPaginator;

    /** @var PaginatorInterface */
    protected $searchAfterPaginator;

    /** @var PrimaryKeyEncrypter */
    protected $primaryKeyEncrypter;

    /** @var array */
    protected $apiConfiguration;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var SimpleFactoryInterface */
    protected $factory;

    /** @var SaverInterface */
    protected $saver;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var ValidatorInterface */
    protected $productModelValidator;

    /** @var AttributeFilterInterface */
    protected $productModelAttributeFilter;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productModelRepository;

    /** @var StreamResourceResponse */
    protected $partialUpdateStreamResource;

    /** @var ListProductModelsQueryValidator */
    private $listProductModelsQueryValidator;

    /** @var ListProductModelsQueryHandler */
    private $listProductModelsQueryHandler;

    /** @var ConnectorProductModelNormalizer */
    private $connectorProductModelNormalizer;

    /** @var GetConnectorProductModels */
    private $getConnectorProductModels;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ApiAggregatorForProductModelPostSaveEventSubscriber */
    private $apiAggregatorForProductModelPostSave;

    /** @var WarmupQueryCache */
    private $warmupQueryCache;

    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderFactoryInterface $pqbSearchAfterFactory,
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $channelRepository,
        PaginatorInterface $offsetPaginator,
        PaginatorInterface $searchAfterPaginator,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        ObjectUpdaterInterface $updater,
        SimpleFactoryInterface $factory,
        SaverInterface $saver,
        UrlGeneratorInterface $router,
        ValidatorInterface $productModelValidator,
        AttributeFilterInterface $productModelAttributeFilter,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        StreamResourceResponse $partialUpdateStreamResource,
        ListProductModelsQueryValidator $listProductModelsQueryValidator,
        ListProductModelsQueryHandler $listProductModelsQueryHandler,
        ConnectorProductModelNormalizer $connectorProductModelNormalizer,
        GetConnectorProductModels $getConnectorProductModels,
        TokenStorageInterface $tokenStorage,
        ApiAggregatorForProductModelPostSaveEventSubscriber $apiAggregatorForProductModelPostSave,
        WarmupQueryCache $warmupQueryCache,
        array $apiConfiguration
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->pqbSearchAfterFactory = $pqbSearchAfterFactory;
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->offsetPaginator = $offsetPaginator;
        $this->searchAfterPaginator = $searchAfterPaginator;
        $this->primaryKeyEncrypter = $primaryKeyEncrypter;
        $this->updater = $updater;
        $this->factory = $factory;
        $this->saver = $saver;
        $this->router = $router;
        $this->productModelValidator = $productModelValidator;
        $this->productModelAttributeFilter = $productModelAttributeFilter;
        $this->productModelRepository = $productModelRepository;
        $this->partialUpdateStreamResource = $partialUpdateStreamResource;
        $this->listProductModelsQueryValidator = $listProductModelsQueryValidator;
        $this->listProductModelsQueryHandler = $listProductModelsQueryHandler;
        $this->connectorProductModelNormalizer = $connectorProductModelNormalizer;
        $this->getConnectorProductModels = $getConnectorProductModels;
        $this->tokenStorage = $tokenStorage;
        $this->apiAggregatorForProductModelPostSave = $apiAggregatorForProductModelPostSave;
        $this->warmupQueryCache = $warmupQueryCache;
        $this->apiConfiguration = $apiConfiguration;
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
        try {
            $user = $this->tokenStorage->getToken()->getUser();
            Assert::isInstanceOf($user, UserInterface::class);

            $productModel = $this->getConnectorProductModels->fromProductModelCode($code, $user->getId());
        } catch (ObjectNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('Product model "%s" does not exist.', $code));
        }

        return new JsonResponse(
            $this->connectorProductModelNormalizer->normalizeConnectorProductModel($productModel)
        );
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
        $productModel = $this->factory->create();

        $this->updateProductModel($productModel, $data, 'post_product_model');
        $this->validateProductModel($productModel);
        $this->saver->save($productModel);

        $response = $this->getResponse($productModel, Response::HTTP_CREATED);

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
        $data['code'] = array_key_exists('code', $data) ? $data['code'] : $code;

        $productModel = $this->productModelRepository->findOneByIdentifier($code);
        $isCreation = null === $productModel;

        if ($isCreation) {
            $this->validateCodeConsistency($code, $data);
            $productModel = $this->factory->create();
        }

        $this->updateProductModel($productModel, $data, 'patch_product_models__code_');
        $this->validateProductModel($productModel);
        $this->saver->save($productModel);

        $status = $isCreation ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;
        $response = $this->getResponse($productModel, $status);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     * @throws ServerErrorResponseException
     *
     * @return JsonResponse
     */
    public function listAction(Request $request): JsonResponse
    {
        $user = $this->tokenStorage->getToken()->getUser();
        Assert::isInstanceOf($user, UserInterface::class);

        $query = new ListProductModelsQuery();

        if ($request->query->has('attributes')) {
            $query->attributeCodes = explode(',', $request->query->get('attributes'));
        }
        if ($request->query->has('locales')) {
            $query->localeCodes = explode(',', $request->query->get('locales'));
        }
        if ($request->query->has('search')) {
            $query->search = json_decode($request->query->get('search'), true);
            if (!is_array($query->search)) {
                throw new UnprocessableEntityHttpException('Search query parameter should be valid JSON.');
            }
        }
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
            $this->listProductModelsQueryValidator->validate($query);
            $productModels = $this->listProductModelsQueryHandler->handle($query); // in try block as PQB is doing validation also
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

        return new JsonResponse($this->normalizeProductModelsList($productModels, $query));
    }

    /**
     * Product models are saved 1 by 1, but we batch events in order to improve performances.
     *
     * @param Request $request
     * @return Response
     * @throws HttpException
     */
    public function partialUpdateListAction(Request $request): Response
    {
        $this->warmupQueryCache->fromRequest($request);
        $resource = $request->getContent(true);
        $this->apiAggregatorForProductModelPostSave->activate();
        $response = $this->partialUpdateStreamResource->streamResponse($resource, [], function () {
            $this->apiAggregatorForProductModelPostSave->dispatchAllEvents();
            $this->apiAggregatorForProductModelPostSave->deactivate();
        });

        return $response;
    }

    protected function getNormalizerOptions(ListProductModelsQuery $query): array
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
        if (array_key_exists('code', $data) && $code !== $data['code']) {
            throw new UnprocessableEntityHttpException(
                sprintf(
                    'The code "%s" provided in the request body must match the code "%s" provided in the url.',
                    $data['code'],
                    $code
                )
            );
        }
    }

    /**
     * Get a response with a location header to the created or updated resource.
     *
     * @param ProductModelInterface $productModel
     * @param int                   $status
     *
     * @return Response
     */
    protected function getResponse(ProductModelInterface $productModel, int $status): Response
    {
        $response = new Response(null, $status);
        $route = $this->router->generate(
            'pim_api_product_model_get',
            ['code' => $productModel->getCode()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $response->headers->set('Location', $route);

        return $response;
    }

    /**
     * Updates product with the provided request data
     *
     * @param ProductModelInterface $productModel
     * @param array                 $data
     * @param string                $anchor
     *
     * @throws DocumentedHttpException
     */
    protected function updateProductModel(ProductModelInterface $productModel, array $data, string $anchor): void
    {
        try {
            if (array_key_exists('values', $data)) {
                $data = $this->productModelAttributeFilter->filter($data);
            }

            $this->updater->update($productModel, $data);
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
     * Validate a product. It throws an error 422 with every violated constraints if
     * the validation failed.
     *
     * @param ProductModelInterface $productModel
     *
     * @throws ViolationHttpException
     */
    protected function validateProductModel(ProductModelInterface $productModel): void
    {
        $violations = $this->productModelValidator->validate($productModel, null, ['Default', 'api']);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }

    private function normalizeProductModelsList(ConnectorProductModelList $connectorProductModels, ListProductModelsQuery $query): array
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
                'list_route_name'     => 'pim_api_product_model_list',
                'item_route_name'     => 'pim_api_product_model_get',
                'item_identifier_key' => 'code',
            ];

            $count = $query->withCountAsBoolean() ? $connectorProductModels->totalNumberOfProductModels() : null;

            return $this->offsetPaginator->paginate(
                $this->connectorProductModelNormalizer->normalizeConnectorProductModelList($connectorProductModels),
                $paginationParameters,
                $count
            );
        } else {
            $productModels = $connectorProductModels->connectorProductModels();
            $lastProductModel = end($productModels);

            $parameters = [
                'query_parameters'    => $queryParameters,
                'search_after'        => [
                    'next' => false !== $lastProductModel ? $this->primaryKeyEncrypter->encrypt($lastProductModel->id()) : null,
                    'self' => $query->searchAfter,
                ],
                'list_route_name'     => 'pim_api_product_model_list',
                'item_route_name'     => 'pim_api_product_model_get',
                'item_identifier_key' => 'code',
            ];

            return $this->searchAfterPaginator->paginate(
                $this->connectorProductModelNormalizer->normalizeConnectorProductModelList($connectorProductModels),
                $parameters,
                null
            );
        }
    }
}
