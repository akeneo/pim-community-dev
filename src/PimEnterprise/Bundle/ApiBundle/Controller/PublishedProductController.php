<?php

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
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Pim\Component\Api\Exception\PaginationParametersException;
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
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Query\Sorter\Directions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PublishedProductController
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
