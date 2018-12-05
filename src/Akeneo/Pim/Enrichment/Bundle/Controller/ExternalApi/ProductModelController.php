<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Pagination\ParameterValidatorInterface;
use Akeneo\Tool\Component\Api\Security\PrimaryKeyEncrypter;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
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
 * @author    Willy MESNAGE <willy.mesnage@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelController
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFromSizeFactory;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbSearchAfterFactory;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

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
    protected $productValidator;

    /** @var AttributeFilterInterface */
    protected $productModelAttributeFilter;

    /** @var ProductModelRepositoryInterface */
    protected $productModelRepository;

    /** @var StreamResourceResponse */
    protected $partialUpdateStreamResource;

    /** @var QueryParametersCheckerInterface */
    protected $queryParametersChecker;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param ProductQueryBuilderFactoryInterface $pqbFromSizeFactory
     * @param ProductQueryBuilderFactoryInterface $pqbSearchAfterFactory
     * @param NormalizerInterface $normalizer
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param QueryParametersCheckerInterface $queryParametersChecker
     * @param ParameterValidatorInterface $parameterValidator
     * @param PaginatorInterface $offsetPaginator
     * @param PaginatorInterface $searchAfterPaginator
     * @param PrimaryKeyEncrypter $primaryKeyEncrypter
     * @param ObjectUpdaterInterface $updater
     * @param SimpleFactoryInterface $factory
     * @param SaverInterface $saver
     * @param UrlGeneratorInterface $router
     * @param ValidatorInterface $productValidator
     * @param AttributeFilterInterface $productModelAttributeFilter
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param StreamResourceResponse $partialUpdateStreamResource
     * @param array $apiConfiguration
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderFactoryInterface $pqbFromSizeFactory,
        ProductQueryBuilderFactoryInterface $pqbSearchAfterFactory,
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $channelRepository,
        QueryParametersCheckerInterface $queryParametersChecker,
        ParameterValidatorInterface $parameterValidator,
        PaginatorInterface $offsetPaginator,
        PaginatorInterface $searchAfterPaginator,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        ObjectUpdaterInterface $updater,
        SimpleFactoryInterface $factory,
        SaverInterface $saver,
        UrlGeneratorInterface $router,
        ValidatorInterface $productValidator,
        AttributeFilterInterface $productModelAttributeFilter,
        ProductModelRepositoryInterface $productModelRepository,
        StreamResourceResponse $partialUpdateStreamResource,
        array $apiConfiguration
    ) {
        $this->pqbFactory                  = $pqbFactory;
        $this->pqbFromSizeFactory          = $pqbFromSizeFactory;
        $this->pqbSearchAfterFactory       = $pqbSearchAfterFactory;
        $this->normalizer                  = $normalizer;
        $this->channelRepository           = $channelRepository;
        $this->queryParametersChecker      = $queryParametersChecker;
        $this->parameterValidator          = $parameterValidator;
        $this->offsetPaginator             = $offsetPaginator;
        $this->searchAfterPaginator        = $searchAfterPaginator;
        $this->primaryKeyEncrypter         = $primaryKeyEncrypter;
        $this->updater                     = $updater;
        $this->factory                     = $factory;
        $this->saver                       = $saver;
        $this->router                      = $router;
        $this->productValidator            = $productValidator;
        $this->productModelAttributeFilter = $productModelAttributeFilter;
        $this->productModelRepository = $productModelRepository;
        $this->partialUpdateStreamResource = $partialUpdateStreamResource;
        $this->apiConfiguration            = $apiConfiguration;
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
        $pqb = $this->pqbFactory->create();
        $pqb->addFilter('identifier', Operators::EQUALS, $code);
        $productModels = $pqb->execute();

        if (0 === $productModels->count()) {
            throw new NotFoundHttpException(sprintf('Product model "%s" does not exist.', $code));
        }

        $productModelApi = $this->normalizer->normalize($productModels->current(), 'external_api');

        return new JsonResponse($productModelApi);
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
        $queryParameters = array_merge(
            [
                'pagination_type' => PaginationTypes::OFFSET,
                'limit' => $this->apiConfiguration['pagination']['limit_by_default'],
            ],
            $request->query->all()
        );

        $paginatedProductModels = PaginationTypes::OFFSET === $queryParameters['pagination_type'] ?
            $this->listOffset($request, $channel, $queryParameters, $normalizerOptions) :
            $this->listSearchAfter($request, $channel, $queryParameters, $normalizerOptions);

        return new JsonResponse($paginatedProductModels);
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

            $normalizerOptions['locales'] = $locales;
        }

        if ($request->query->has('attributes')) {
            $attributes = explode(',', $request->query->get('attributes'));
            $this->queryParametersChecker->checkAttributesParameters($attributes);

            $normalizerOptions['attributes'] = $attributes;
        }

        return $normalizerOptions;
    }

    /**
     * Get list of product models using 'offset' pagination mode.
     *
     * @param Request $request
     * @param null|ChannelInterface $channel
     * @param array $queryParameters
     * @param array $normalizerOptions
     * @return array
     *
     * @throws DocumentedHttpException
     */
    protected function listOffset(
        Request $request,
        ?ChannelInterface $channel,
        array $queryParameters,
        array $normalizerOptions
    ) {
        $from = isset($queryParameters['page']) ? ($queryParameters['page'] - 1) * $queryParameters['limit'] : 0;

        $pqb = $this->pqbFromSizeFactory->create(['limit' => (int) $queryParameters['limit'], 'from' => (int) $from]);

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
        $productModels = $pqb->execute();

        $paginationParameters = [
            'query_parameters'    => $queryParameters,
            'list_route_name'     => 'pim_api_product_model_list',
            'item_route_name'     => 'pim_api_product_model_get',
            'item_identifier_key' => 'code',
        ];

        $count = 'true' === $queryParameters['with_count'] ? $productModels->count() : null;
        $paginatedProductModels = $this->offsetPaginator->paginate(
            $this->normalizer->normalize($productModels, 'external_api', $normalizerOptions),
            $paginationParameters,
            $count
        );

        return $paginatedProductModels;
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
        $violations = $this->productValidator->validate($productModel, null, ['Default', 'api']);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }

    /**
     * Get list of product models using 'search after' pagination mode.
     *
     * @param Request $request
     * @param null|ChannelInterface $channel
     * @param array $queryParameters
     * @param array $normalizerOptions
     *
     * @return array
     */
    protected function listSearchAfter(
        Request $request,
        ?ChannelInterface $channel,
        array $queryParameters,
        array $normalizerOptions
    ) {
        $pqbOptions = ['limit' => (int) $queryParameters['limit']];
        $searchParameterCrypted = null;
        if (isset($queryParameters['search_after'])) {
            $searchParameterCrypted = $queryParameters['search_after'];
            $searchParameterDecrypted = $this->primaryKeyEncrypter->decrypt($queryParameters['search_after']);
            $pqbOptions['search_after_unique_key'] = $searchParameterDecrypted;
            $pqbOptions['search_after'] = [$searchParameterDecrypted];
        }
        $pqb = $this->pqbSearchAfterFactory->create($pqbOptions);

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
        $productModelCursor = $pqb->execute();

        $productModels = [];
        foreach ($productModelCursor as $productModel) {
            $productModels[] = $productModel;
        }

        $lastProductModel = end($productModels);
        reset($productModels);

        $nextSearchAfter = false !== $lastProductModel ?
            $this->primaryKeyEncrypter->encrypt($lastProductModel->getId()) :
            null;

        $parameters = [
            'query_parameters'    => $queryParameters,
            'search_after'        => [
                'next' => $nextSearchAfter,
                'self' => $searchParameterCrypted,
            ],
            'list_route_name'     => 'pim_api_product_model_list',
            'item_route_name'     => 'pim_api_product_model_get',
            'item_identifier_key' => 'code',
        ];

        $paginatedProductModels = $this->searchAfterPaginator->paginate(
            $this->normalizer->normalize($productModels, 'external_api', $normalizerOptions),
            $parameters,
            null
        );

        return $paginatedProductModels;
    }

    /**
     * Set the PQB filters.
     * If a scope is requested, add a filter to return only product models linked to its category tree
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

                if (in_array($propertyCode, ['created', 'updated'])) {
                    if (Operators::BETWEEN === $filter['operator'] && is_array($value)) {
                        $values = [];
                        foreach ($value as $date) {
                            $values[] = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
                        }
                        $value = $values;
                    } else {
                        //PIM-7541 Create the date with the server timezone configuration. Do not force it to UTC timezone.
                        $value = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                    }
                }

                $this->queryParametersChecker->checkPropertyParameters($propertyCode, $filter['operator']);

                $pqb->addFilter($propertyCode, $filter['operator'], $value, $context);
            }
        }
    }
}
