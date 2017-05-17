<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\ApiBundle\Documentation;
use Pim\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Pim\Component\Api\Exception\DocumentedHttpException;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Api\Pagination\PaginationTypes;
use Pim\Component\Api\Pagination\PaginatorInterface;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Api\Repository\AttributeRepositoryInterface;
use Pim\Component\Api\Repository\ProductRepositoryInterface;
use Pim\Component\Api\Security\PrimaryKeyEncrypter;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Exception\UnsupportedFilterException;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Query\Sorter\Directions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\RouterInterface;
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
    protected $pqbFactory;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

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

    /** @var RouterInterface */
    protected $router;

    /** @var ProductFilterInterface */
    protected $emptyValuesFilter;

    /** @var StreamResourceResponse */
    protected $partialUpdateStreamResource;

    /** @var  PrimaryKeyEncrypter */
    protected $primaryKeyEncrypter;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param ProductQueryBuilderFactoryInterface   $pqbFactory
     * @param NormalizerInterface                   $normalizer
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param AttributeRepositoryInterface          $attributeRepository
     * @param ProductRepositoryInterface            $productRepository
     * @param PaginatorInterface                    $searchAfterPaginator
     * @param ParameterValidatorInterface           $parameterValidator
     * @param ValidatorInterface                    $productValidator
     * @param ProductBuilderInterface               $productBuilder
     * @param RemoverInterface                      $remover
     * @param ObjectUpdaterInterface                $updater
     * @param SaverInterface                        $saver
     * @param RouterInterface                       $router
     * @param ProductFilterInterface                $emptyValuesFilter
     * @param StreamResourceResponse                $partialUpdateStreamResource
     * @param PrimaryKeyEncrypter                   $primaryKeyEncrypter
     * @param array                                 $apiConfiguration
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        AttributeRepositoryInterface $attributeRepository,
        ProductRepositoryInterface $productRepository,
        PaginatorInterface $searchAfterPaginator,
        ParameterValidatorInterface $parameterValidator,
        ValidatorInterface $productValidator,
        ProductBuilderInterface $productBuilder,
        RemoverInterface $remover,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        RouterInterface $router,
        ProductFilterInterface $emptyValuesFilter,
        StreamResourceResponse $partialUpdateStreamResource,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        array $apiConfiguration
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
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
        $this->apiConfiguration = $apiConfiguration;
    }

    /**
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        try {
            $pagination = $request->query->has('pagination_type') &&
                PaginationTypes::OFFSET === $request->query->get('pagination_type') ?
                PaginationTypes::SEARCH_AFTER : $request->query->get('pagination_type');

            $this->parameterValidator->validate(
                array_merge($request->query->all(), [$pagination]),
                ['support_search_after' => true]
            );
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

        $queryParameters = array_merge([
            'limit' => $this->apiConfiguration['pagination']['limit_by_default']
        ], $request->query->all());
        $pqbOptions = ['limit' => (int)$queryParameters['limit']];

        $searchParameter = null;
        if (isset($queryParameters['search_after'])) {
            $searchParameter = $queryParameters['search_after'];
        } elseif (isset($queryParameters['search_before']) && '' !== $queryParameters['search_before']) {
            $searchParameter = $queryParameters['search_before'];
        }

        if (null !== $searchParameter) {
            $searchParameterDecrypted = $this->primaryKeyEncrypter->decrypt($searchParameter);
            $pqbOptions['search_after_unique_key'] = $searchParameterDecrypted;
            $pqbOptions['search_after'] = [$searchParameterDecrypted];
        }

        $pqb = $this->pqbFactory->create($pqbOptions);

        try {
            $this->setPQBFilters($pqb, $request, $channel);
        } catch (PropertyException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (UnsupportedFilterException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (InvalidOperatorException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (ObjectNotFoundException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $paginatedProducts = $this->searchAfterIdentifier($pqb, $queryParameters, $normalizerOptions, $searchParameter);

        return new JsonResponse($paginatedProducts);
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function getAction($code)
    {
        $product = $this->productRepository->findOneByIdentifier($code);
        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist.', $code));
        }

        $productApi = $this->normalizer->normalize($product, 'external_api');

        return new JsonResponse($productApi);
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function deleteAction($code)
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
    public function createAction(Request $request)
    {
        $data = $this->getDecodedContent($request->getContent());

        $data = $this->populateIdentifierProductValue($data);

        $product = $this->productBuilder->createProduct();

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
    public function partialUpdateAction(Request $request, $code)
    {
        $data = $this->getDecodedContent($request->getContent());

        $product = $this->productRepository->findOneByIdentifier($code);
        $isCreation = null === $product;

        if ($isCreation) {
            $this->validateCodeConsistency($code, $data);
            $product = $this->productBuilder->createProduct($code);
        }

        $data['identifier'] = array_key_exists('identifier', $data) ? $data['identifier'] : $code;
        $data = $this->populateIdentifierProductValue($data);

        if (!$isCreation) {
            $data = $this->filterEmptyValues($product, $data);
        }

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
    public function partialUpdateListAction(Request $request)
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
    protected function getDecodedContent($content)
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
    protected function updateProduct(ProductInterface $product, array $data, $anchor)
    {
        try {
            $this->updater->update($product, $data);
        } catch (PropertyException $exception) {
            throw new DocumentedHttpException(
                Documentation::URL . $anchor,
                sprintf('%s Check the standard format documentation.', $exception->getMessage()),
                $exception
            );
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
    protected function filterEmptyValues(ProductInterface $product, array $data)
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
                    'Property "%s" does not exist. Check the standard format documentation.',
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
    protected function validateProduct(ProductInterface $product)
    {
        $violations = $this->productValidator->validate($product);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }

    /**
     * Get a response with a location header to the created or updated resource.
     *
     * @param ProductInterface $product
     * @param string           $status
     *
     * @return Response
     */
    protected function getResponse(ProductInterface $product, $status)
    {
        $response = new Response(null, $status);
        $route = $this->router->generate('pim_api_product_get', ['code' => $product->getIdentifier()], true);
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
        ChannelInterface $channel = null
    ) {
        $search = [];

        if ($request->query->has('search')) {
            $search = json_decode($request->query->get('search'), true);
            if (null === $search) {
                throw new UnprocessableEntityHttpException('Search query parameter should be valid JSON.');
            }

            if (!is_array($search)) {
                throw new UnprocessableEntityHttpException(
                    sprintf('Search query parameter has to be an array, "%s" given.', gettype($search))
                );
            }
        }

        if (null !== $channel && !isset($search['categories'])) {
            $search['categories'] = [
                [
                    'operator' => Operators::IN_CHILDREN_LIST,
                    'value'    => [$channel->getCategory()->getCode()]
                ]
            ];
        }

        foreach ($search as $propertyCode => $filters) {
            if (!is_array($filters) || !isset($filters[0])) {
                throw new UnprocessableEntityHttpException(
                    sprintf(
                        'Structure of filter "%s" should respect this structure: %s',
                        $propertyCode,
                        sprintf('{"%s":[{"operator": "my_operator", "value": "my_value"}]}', $propertyCode)
                    )
                );
            }

            foreach ($filters as $filter) {
                if (!isset($filter['operator'])) {
                    throw new UnprocessableEntityHttpException(
                        sprintf('Operator is missing for the property "%s".', $propertyCode)
                    );
                }

                if (!is_string($filter['operator'])) {
                    throw new UnprocessableEntityHttpException(
                        sprintf('Operator has to be a string, "%s" given.', gettype($filter['operator']))
                    );
                }

                $context['locale'] = isset($filter['locale']) ? $filter['locale'] : $request->query->get('search_locale');
                $context['scope'] = isset($filter['scope']) ? $filter['scope'] : $request->query->get('search_scope');

                if (isset($filter['locales'])) {
                    $context['locales'] = $filter['locales'];
                }

                $value = isset($filter['value']) ? $filter['value'] : null;

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
    protected function getNormalizerOptions(Request $request, ChannelInterface $channel = null)
    {
        $normalizerOptions = [];

        if ($request->query->has('scope')) {
            $normalizerOptions['channels'] = [$channel->getCode()];
            $normalizerOptions['locales'] = $channel->getLocaleCodes();
        }

        if ($request->query->has('locales')) {
            $this->checkLocalesParameters($request->query->get('locales'), $channel);

            $normalizerOptions['locales'] = explode(',', $request->query->get('locales'));
        }

        if ($request->query->has('attributes')) {
            $this->checkAttributesParameters($request->query->get('attributes'));

            $normalizerOptions['attributes'] = explode(',', $request->query->get('attributes'));
        }

        return $normalizerOptions;
    }

    /**
     * Checks $localeCodes if they exist.
     * Throws an exception if one of them does not exist or, if there is a $channel, one of them does not belong to it.
     *
     * @param string                $localeCodes
     * @param ChannelInterface|null $channel
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function checkLocalesParameters($localeCodes, ChannelInterface $channel = null)
    {
        $locales = explode(',', $localeCodes);

        $errors = [];
        foreach ($locales as $locale) {
            if (null === $this->localeRepository->findOneByIdentifier($locale)) {
                $errors[] = $locale;
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ? 'Locales "%s" do not exist.' : 'Locale "%s" does not exist.';
            throw new UnprocessableEntityHttpException(sprintf($plural, implode(', ', $errors)));
        }

        if (null !== $channel) {
            if ($diff = array_diff($locales, $channel->getLocaleCodes())) {
                $plural = sprintf(count($diff) > 1 ? 'Locales "%s" are' : 'Locale "%s" is', implode(', ', $diff));
                throw new UnprocessableEntityHttpException(
                    sprintf('%s not activated for the scope "%s".', $plural, $channel->getCode())
                );
            }
        }
    }

    /**
     * Checks $attributes if they exist. Thrown an exception if one of them does not exist.
     *
     * @param string $attributes
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function checkAttributesParameters($attributes)
    {
        $attributeCodes = explode(',', $attributes);

        $errors = [];
        foreach ($attributeCodes as $attributeCode) {
            if (null === $this->attributeRepository->findOneByIdentifier($attributeCode)) {
                $errors[] = $attributeCode;
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ? 'Attributes "%s" do not exist.' : 'Attribute "%s" does not exist.';
            throw new UnprocessableEntityHttpException(sprintf($plural, implode(', ', $errors)));
        }
    }

    /**
     * Add to the data the identifier product value with the same identifier as the value of the identifier property.
     * It silently overwrite the identifier product value if one is already provided in the input.
     *
     * @param array $data
     *
     * @return array
     */
    protected function populateIdentifierProductValue(array $data)
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
    protected function validateCodeConsistency($code, array $data)
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
        $searchParameter
    ) {
        $direction = isset($queryParameters['search_before']) ? Directions::DESCENDING : Directions::ASCENDING;
        $pqb->addSorter('id', $direction);

        $productCursor = $pqb->execute();
        $products = [];
        foreach ($productCursor as $product) {
            $products[] = $product;
        }

        if (isset($queryParameters['search_before'])) {
            $products = array_reverse($products);
        }

        $parameters = [
            'query_parameters'    => $queryParameters,
            'list_route_name'     => 'pim_api_product_list',
            'item_route_name'     => 'pim_api_product_get',
            'item_identifier_key' => 'identifier',
        ];

        $parameters['search_after']['self'] = $searchParameter;
        $parameters['search_after']['next'] = !empty($products) ? $this->primaryKeyEncrypter->encrypt(end($products)->getId()) : '';
        $parameters['search_after']['previous'] = !empty($products) ? $this->primaryKeyEncrypter->encrypt(reset($products)->getId()) : '';

        $count = isset($queryParameters['with_count']) && 'true' === $queryParameters['with_count'] ?
            $productCursor->count() : null;
        $products = $this->normalizer->normalize($products, 'external_api', $normalizerOptions);

        $paginatedProducts = $this->searchAfterPaginator->paginate($products, $parameters, $count);

        return $paginatedProducts;
    }
}
