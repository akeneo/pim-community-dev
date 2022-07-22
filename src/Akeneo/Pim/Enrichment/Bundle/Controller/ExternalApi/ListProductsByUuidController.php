<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsByUuidQueryHandler;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ListProductsQueryValidator;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductWithUuidNormalizer;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\ServerErrorResponseException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListProductsByUuidController
{
    public function __construct(
        private PaginatorInterface $offsetPaginator,
        private PaginatorInterface $searchAfterPaginator,
        private ListProductsQueryValidator $listProductsQueryValidator,
        private array $apiConfiguration,
        private ListProductsByUuidQueryHandler $listProductsByUuidQueryHandler,
        private ConnectorProductWithUuidNormalizer $connectorProductWithUuidNormalizer,
        private TokenStorageInterface $tokenStorage,
        private SecurityFacade $security
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->security->isGranted('pim_api_product_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list products.');
        }

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
            $products = $this->listProductsByUuidQueryHandler->handle($query); // in try block as PQB is doing validation also
        } catch (InvalidQueryException $e) {
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
                'list_route_name' => 'pim_api_product_uuid_list',
                'item_route_name' => 'pim_api_product_uuid_get',
                'item_route_parameter' => 'uuid',
                'item_identifier_key' => 'uuid',
            ];

            $count = $query->withCountAsBoolean() ? $connectorProductList->totalNumberOfProducts() : null;

            return $this->offsetPaginator->paginate(
                $this->connectorProductWithUuidNormalizer->normalizeConnectorProductList($connectorProductList),
                $paginationParameters,
                $count
            );
        } else {
            $connectorProducts = $connectorProductList->connectorProducts();
            $lastProduct = end($connectorProducts);

            $parameters = [
                'query_parameters' => $queryParameters,
                'search_after' => [
                    'next' => false !== $lastProduct ? $lastProduct->uuid()->toString() : null,
                    'self' => $query->searchAfter,
                ],
                'list_route_name' => 'pim_api_product_uuid_list',
                'item_route_name' => 'pim_api_product_uuid_get',
                'item_route_parameter' => 'uuid',
                'item_identifier_key' => 'uuid',
            ];

            return $this->searchAfterPaginator->paginate(
                $this->connectorProductWithUuidNormalizer->normalizeConnectorProductList($connectorProductList),
                $parameters,
                null
            );
        }
    }
}
