<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ListProductsQueryHandler
{
    /** @var ApplyProductSearchQueryParametersToPQB */
    private $applyProductSearchQueryParametersToPQB;

    /** @var ProductQueryBuilderFactoryInterface */
    private $fromSizePqbFactory;

    /** @var ProductQueryBuilderFactoryInterface */
    private $searchAfterPqbFactory;

    /** @var GetConnectorProducts */
    private $getConnectorProductsQuery;

    /** @var GetConnectorProducts */
    private $getConnectorProductsQuerywithOptions;

    /** @var IdentifiableObjectRepositoryInterface */
    private $channelRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    private GetProductsWithQualityScoresInterface $getProductsWithQualityScores;

    private GetProductsWithCompletenessesInterface $getProductsWithCompletenesses;

    public function __construct(
        IdentifiableObjectRepositoryInterface $channelRepository,
        ApplyProductSearchQueryParametersToPQB $applyProductSearchQueryParametersToPQB,
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        GetConnectorProducts $getConnectorProductsQuery,
        GetConnectorProducts $getConnectorProductsQuerywithOptions,
        EventDispatcherInterface $eventDispatcher,
        GetProductsWithQualityScoresInterface $getProductsWithQualityScores,
        GetProductsWithCompletenessesInterface $getProductsWithCompletenesses,
    ) {
        $this->channelRepository = $channelRepository;
        $this->applyProductSearchQueryParametersToPQB = $applyProductSearchQueryParametersToPQB;
        $this->fromSizePqbFactory = $fromSizePqbFactory;
        $this->searchAfterPqbFactory = $searchAfterPqbFactory;
        $this->getConnectorProductsQuery = $getConnectorProductsQuery;
        $this->getConnectorProductsQuerywithOptions = $getConnectorProductsQuerywithOptions;
        $this->eventDispatcher = $eventDispatcher;
        $this->getProductsWithQualityScores = $getProductsWithQualityScores;
        $this->getProductsWithCompletenesses = $getProductsWithCompletenesses;
    }

    /**
     * @param ListProductsQuery $query
     * @return ConnectorProductList
     */
    public function handle(ListProductsQuery $query): ConnectorProductList
    {
        $pqb = $this->getSearchPQB($query);

        $pqb->addFilter('identifier', Operators::IS_NOT_EMPTY, null);

        try {
            $this->applyProductSearchQueryParametersToPQB->apply(
                $pqb,
                $query->search,
                $query->channelCode,
                $query->searchLocaleCode,
                $query->searchChannelCode
            );
        } catch (
            UnsupportedFilterException
            | PropertyException
            | InvalidOperatorException
            | ObjectNotFoundException
            $e
        ) {
            throw new InvalidQueryException($e->getMessage(), $e->getCode(), $e);
        }

        $pqb->addSorter('identifier', Directions::ASCENDING);

        $connectorProductsQuery = $query->withAttributeOptionsAsBoolean() ?
            $this->getConnectorProductsQuerywithOptions :
            $this->getConnectorProductsQuery;

        $queryLocales = $this->getLocales($query->channelCode, $query->localeCodes);

        $connectorProductList = $connectorProductsQuery->fromProductQueryBuilder(
            $pqb,
            $query->userId,
            $query->attributeCodes,
            $query->channelCode,
            $queryLocales,
        );

        $this->eventDispatcher->dispatch(new ReadProductsEvent(count($connectorProductList->connectorProducts())));

        if ($query->withQualityScores()) {
            $connectorProductList = $this->getProductsWithQualityScores->fromConnectorProductList(
                $connectorProductList,
                $query->channelCode,
                $queryLocales ?? []
            );
        }
        if ($query->withCompletenesses()) {
            $connectorProductList = $this->getProductsWithCompletenesses->fromConnectorProductList(
                $connectorProductList,
                $query->channelCode,
                $queryLocales ?? []
            );
        }

        return $connectorProductList;
    }

    private function getSearchPQB(ListProductsQuery $query): ProductQueryBuilderInterface
    {
        if (PaginationTypes::OFFSET === $query->paginationType) {
            return $this->fromSizePqbFactory->create([
                'limit' => (int)$query->limit,
                'from' => ($query->page - 1) * $query->limit
            ]);
        }
        $pqbOptions = ['limit' => (int)$query->limit];

        if (null !== $query->searchAfter) {
            // @TODO CPM-596: use product_<uuid> once the uuid migration will be done
            // Today we cannot use it, because during the migration some products are indexed with id, and others by uuid
            $pqbOptions['search_after_unique_key'] = 'product_z';
            $pqbOptions['search_after'] = [\mb_strtolower($query->searchAfter)];
        }

        return $this->searchAfterPqbFactory->create($pqbOptions);
    }

    private function getLocales(?string $channelCodeToFilterValuesOn, ?array $localeCodesToFilterValuesOn): ?array
    {
        if (null === $channelCodeToFilterValuesOn) {
            return $localeCodesToFilterValuesOn;
        } else {
            if (null === $localeCodesToFilterValuesOn) {
                $channel = $this->channelRepository->findOneByIdentifier($channelCodeToFilterValuesOn);

                return $channel->getLocaleCodes();
            } else {
                return $localeCodesToFilterValuesOn;
            }
        }
    }
}
