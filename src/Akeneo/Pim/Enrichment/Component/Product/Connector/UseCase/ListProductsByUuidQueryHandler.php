<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ListProductsByUuidQueryHandler
{
    public function __construct(
        private IdentifiableObjectRepositoryInterface $channelRepository,
        private ApplyProductSearchQueryParametersToPQB $applyProductSearchQueryParametersToPQB,
        private ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        private ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        private GetConnectorProducts $getConnectorProductsQuery,
        private GetConnectorProducts $getConnectorProductsQuerywithOptions,
        private EventDispatcherInterface $eventDispatcher,
        private GetProductsWithQualityScoresInterface $getProductsWithQualityScores,
        private GetProductsWithCompletenessesInterface $getProductsWithCompletenesses,
    ) {
    }

    public function handle(ListProductsQuery $query): ConnectorProductList
    {
        $pqb = $this->getSearchPQB($query);

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

        $pqb->addSorter('id', Directions::ASCENDING);

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
            $searchAfter = Uuid::isValid($query->searchAfter) ? Uuid::fromString($query->searchAfter)->toString() : $query->searchAfter;
            $pqbOptions['search_after'] = [sprintf('product_%s', $searchAfter)];
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
