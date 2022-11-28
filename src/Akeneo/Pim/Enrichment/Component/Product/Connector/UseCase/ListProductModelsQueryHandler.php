<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ListProductModelsQueryHandler
{
    public function __construct(
        private ApplyProductSearchQueryParametersToPQB $applyProductSearchQueryParametersToPQB,
        private ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        private ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        private GetConnectorProductModels $getConnectorProductModelsQuery,
        private IdentifiableObjectRepositoryInterface $channelRepository,
        private FindId $getProductModelId,
        private GetProductModelsWithQualityScoresInterface $getProductModelsWithQualityScores
    ) {
    }

    public function handle(ListProductModelsQuery $query): ConnectorProductModelList
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

        $pqb->addSorter('identifier', Directions::ASCENDING);

        $locales = $this->getLocales($query->channelCode, $query->localeCodes);

        $productModelList = $this->getConnectorProductModelsQuery->fromProductQueryBuilder(
            $pqb,
            $query->userId,
            $query->attributeCodes,
            $query->channelCode,
            $locales
        );

        if ($query->withQualityScores()) {
            $productModelList = $this->getProductModelsWithQualityScores->fromConnectorProductModelList(
                $productModelList,
                $query->channelCode,
                $locales ?? []
            );
        }

        return $productModelList;
    }

    private function getSearchPQB(ListProductModelsQuery $query): ProductQueryBuilderInterface
    {
        if (PaginationTypes::OFFSET === $query->paginationType) {
            return $this->fromSizePqbFactory->create([
                'limit' => (int) $query->limit,
                'from' => ($query->page - 1) * $query->limit
            ]);
        }

        $pqbOptions = ['limit' => (int) $query->limit];

        if (null !== $query->searchAfter) {
            $id = $this->getProductModelId->fromIdentifier($query->searchAfter);
            $pqbOptions['search_after_unique_key'] = null === $id ? '' : \sprintf('product_model_%s', $id);
            $pqbOptions['search_after'] = [\mb_strtolower($query->searchAfter)];
        }

        return $this->searchAfterPqbFactory->create($pqbOptions);
    }

    private function getLocales(?string $channelCodeToFilterValuesOn, ?array $localeCodesToFilterValuesOn): ?array
    {
        if (null === $channelCodeToFilterValuesOn) {
            return $localeCodesToFilterValuesOn;
        }

        if (null === $localeCodesToFilterValuesOn) {
            return $this->channelRepository->findOneByIdentifier($channelCodeToFilterValuesOn)->getLocaleCodes();
        }

        return $localeCodesToFilterValuesOn;
    }
}
