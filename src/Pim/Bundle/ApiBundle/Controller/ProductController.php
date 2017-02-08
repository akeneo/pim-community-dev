<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\CatalogBundle\Version;
use Pim\Component\Api\Exception\DocumentedHttpException;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Api\Pagination\HalPaginator;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Catalog\Exception\UnsupportedFilterException;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var HalPaginator */
    protected $paginator;

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

    /** @var string */
    protected $urlDocumentation;

    /**
     * @param ProductQueryBuilderFactoryInterface   $pqbFactory
     * @param NormalizerInterface                   $normalizer
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param ProductRepositoryInterface            $productRepository
     * @param HalPaginator                          $paginator
     * @param ParameterValidatorInterface           $parameterValidator
     * @param ValidatorInterface                    $productValidator
     * @param ProductBuilderInterface               $productBuilder
     * @param RemoverInterface                      $remover
     * @param ObjectUpdaterInterface                $updater
     * @param SaverInterface                        $saver
     * @param RouterInterface                       $router
     * @param ProductFilterInterface                $emptyValuesFilter
     * @param string                                $urlDocumentation
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ProductRepositoryInterface $productRepository,
        HalPaginator $paginator,
        ParameterValidatorInterface $parameterValidator,
        ValidatorInterface $productValidator,
        ProductBuilderInterface $productBuilder,
        RemoverInterface $remover,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        RouterInterface $router,
        ProductFilterInterface $emptyValuesFilter,
        $urlDocumentation
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
        $this->paginator = $paginator;
        $this->parameterValidator = $parameterValidator;
        $this->productValidator = $productValidator;
        $this->productBuilder = $productBuilder;
        $this->remover = $remover;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->router = $router;
        $this->emptyValuesFilter = $emptyValuesFilter;
        $this->urlDocumentation = sprintf($urlDocumentation, substr(Version::VERSION, 0, 3));
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
        $queryParameters = [];
        $queryParameters['page'] = $request->query->get('page', 1);
        $queryParameters['limit'] = $request->query->get('limit', 10); // limit will be put in config in an other PR

        try {
            $this->parameterValidator->validate($queryParameters);
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $channel = null;
        if ($request->query->has('channel')) {
            $channel = $this->channelRepository->findOneByIdentifier($request->query->get('channel'));
            if (null === $channel) {
                throw new UnprocessableEntityHttpException(
                    sprintf('Channel "%s" does not exist.', $request->query->get('channel'))
                );
            }
        }

        $normalizerOptions = $this->getNormalizerOptions($request, $channel);

        $pqb = $this->pqbFactory->create([]);
        try {
            $this->setPQBFilters($pqb, $request, $channel);
        } catch (PropertyException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (UnsupportedFilterException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $count = $this->productRepository->count($pqb->getQueryBuilder());

        $pqb->getQueryBuilder()
            ->setMaxResults($queryParameters['limit'])
            ->setFirstResult(($queryParameters['page'] - 1) * $queryParameters['limit']);

        $productsApi = $this->normalizer->normalize($pqb->execute(), 'external_api', $normalizerOptions);
        $paginatedProducts = $this->paginator->paginate(
            $productsApi,
            array_merge($request->query->all(), $queryParameters),
            $count,
            'pim_api_product_list',
            'pim_api_product_get',
            'identifier'
        );

        return new JsonResponse($paginatedProducts);
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
        $product = $this->productRepository->findOneByIdentifier($code);
        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist.', $code));
        }

        $productApi = $this->normalizer->normalize($product, 'external_api');

        return new JsonResponse($productApi);
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $code)
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

        $this->updateProduct($product, $data);
        $this->validateProduct($product);
        $this->saver->save($product);

        $response = $this->getCreateResponse($product);

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
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function updateProduct(ProductInterface $product, array $data)
    {
        try {
            $this->updater->update($product, $data);
        } catch (UnknownPropertyException $exception) {
            throw new DocumentedHttpException(
                $this->urlDocumentation,
                sprintf(
                    'Property "%s" does not exist. Check the standard format documentation.',
                    $exception->getPropertyName()
                ),
                $exception
            );
        } catch (PropertyException $exception) {
            throw new DocumentedHttpException(
                $this->urlDocumentation,
                sprintf('%s Check the standard format documentation.', $exception->getMessage()),
                $exception
            );
        }
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
     * Get a response with HTTP code 201 when an object is created.
     *
     * @param ProductInterface $product
     *
     * @return Response
     */
    protected function getCreateResponse(ProductInterface $product)
    {
        $response = new Response(null, Response::HTTP_CREATED);
        $route = $this->router->generate('pim_api_product_get', ['code' => $product->getIdentifier()], true);
        $response->headers->set('Location', $route);

        return $response;
    }

    /**
     * Set the PQB filters.
     * If a channel is requested, add a filter to return only products linked to its category tree
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
                $context['scope'] = isset($filter['scope']) ? $filter['scope'] : $request->query->get('search_channel');
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

        if ($request->query->has('channel')) {
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
                    sprintf('%s not activated for the channel "%s".', $plural, $channel->getCode())
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
        $identifierProperty = $this->productRepository->getIdentifierProperties()[0];
        $identifier = isset($data['identifier']) ? $data['identifier'] : null;

        unset($data['values'][$identifierProperty]);

        $data['values'][$identifierProperty][] = [
            'locale' => null,
            'scope'  => null,
            'data'   => $identifier,
        ];

        return $data;
    }
}
