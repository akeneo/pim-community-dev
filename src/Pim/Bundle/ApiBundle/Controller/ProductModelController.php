<?php

declare(strict_types=1);

namespace Pim\Bundle\ApiBundle\Controller;

use Elasticsearch\Common\Exceptions\ServerErrorResponseException;
use Pim\Bundle\ApiBundle\Documentation;
use Pim\Component\Api\Exception\DocumentedHttpException;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Pagination\PaginationTypes;
use Pim\Component\Api\Pagination\PaginatorInterface;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Api\Security\PrimaryKeyEncrypter;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\Sorter\Directions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param ProductQueryBuilderFactoryInterface $pqbFromSizeFactory
     * @param ProductQueryBuilderFactoryInterface $pqbSearchAfterFactory
     * @param NormalizerInterface                 $normalizer
     * @param ParameterValidatorInterface         $parameterValidator
     * @param PaginatorInterface                  $offsetPaginator
     * @param PaginatorInterface                  $searchAfterPaginator
     * @param PrimaryKeyEncrypter                 $primaryKeyEncrypter
     * @param array                               $apiConfiguration
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderFactoryInterface $pqbFromSizeFactory,
        ProductQueryBuilderFactoryInterface $pqbSearchAfterFactory,
        NormalizerInterface $normalizer,
        ParameterValidatorInterface $parameterValidator,
        PaginatorInterface $offsetPaginator,
        PaginatorInterface $searchAfterPaginator,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        array $apiConfiguration
    ) {
        $this->pqbFactory            = $pqbFactory;
        $this->pqbFromSizeFactory    = $pqbFromSizeFactory;
        $this->pqbSearchAfterFactory = $pqbSearchAfterFactory;
        $this->normalizer            = $normalizer;
        $this->parameterValidator    = $parameterValidator;
        $this->offsetPaginator       = $offsetPaginator;
        $this->searchAfterPaginator  = $searchAfterPaginator;
        $this->primaryKeyEncrypter   = $primaryKeyEncrypter;
        $this->apiConfiguration      = $apiConfiguration;
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

        $productModelApi = $this->normalizer->normalize($productModels->current(), 'standard');

        return new JsonResponse($productModelApi);
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

        $queryParameters = array_merge(
            [
                'pagination_type' => PaginationTypes::OFFSET,
                'limit' => $this->apiConfiguration['pagination']['limit_by_default'],
            ],
            $request->query->all()
        );

        $paginatedProductModels = PaginationTypes::OFFSET === $queryParameters['pagination_type'] ?
            $this->listOffset($queryParameters) :
            $this->listSearchAfter($queryParameters);

        return new JsonResponse($paginatedProductModels);
    }

    /**
     * Get list of product models using 'offset' pagination mode.
     *
     * @param array $queryParameters
     *
     * @throws DocumentedHttpException
     * @throws ServerErrorResponseException
     *
     * @return array
     */
    protected function listOffset(array $queryParameters)
    {
        $from = isset($queryParameters['page']) ? ($queryParameters['page'] - 1) * $queryParameters['limit'] : 0;

        $pqb = $this->pqbFromSizeFactory->create(['limit' => (int) $queryParameters['limit'], 'from' => (int) $from]);
        $queryParameters = array_merge(['page' => 1, 'with_count' => 'false'], $queryParameters);
        $pqb->addSorter('id', Directions::ASCENDING);
        $productModels = $pqb->execute();

        $paginationParameters = [
            'query_parameters'    => $queryParameters,
            'list_route_name'     => 'pim_api_product_model_list',
            'item_route_name'     => 'pim_api_product_model_get',
            'item_identifier_key' => 'code',
        ];

        try {
            $count = 'true' === $queryParameters['with_count'] ? $productModels->count() : null;
            $paginatedProductModels = $this->offsetPaginator->paginate(
                $this->normalizer->normalize($productModels, 'standard'),
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

        return $paginatedProductModels;
    }

    /**
     * Get list of product models using 'search after' pagination mode.
     *
     * @param array $queryParameters
     *
     * @return array
     */
    protected function listSearchAfter(array $queryParameters)
    {
        $pqbOptions = ['limit' => (int) $queryParameters['limit']];
        $searchParameterCrypted = null;
        if (isset($queryParameters['search_after'])) {
            $searchParameterCrypted = $queryParameters['search_after'];
            $searchParameterDecrypted = $this->primaryKeyEncrypter->decrypt($queryParameters['search_after']);
            $pqbOptions['search_after_unique_key'] = $searchParameterDecrypted;
            $pqbOptions['search_after'] = [$searchParameterDecrypted];
        }
        $pqb = $this->pqbSearchAfterFactory->create($pqbOptions);

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
            $this->normalizer->normalize($productModels, 'standard'),
            $parameters,
            null
        );

        return $paginatedProductModels;
    }
}
