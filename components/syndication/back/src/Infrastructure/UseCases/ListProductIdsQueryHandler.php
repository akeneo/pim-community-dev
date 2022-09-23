<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Infrastructure\UseCases;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ApplyProductSearchQueryParametersToPQB;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ListProductIdsQueryHandler
{
    private ApplyProductSearchQueryParametersToPQB $applyProductSearchQueryParametersToPQB;
    private ProductQueryBuilderFactoryInterface $fromSizePqbFactory;
    private ProductQueryBuilderFactoryInterface $searchAfterPqbFactory;

    private FindId $getProductId;

    public function __construct(
        ApplyProductSearchQueryParametersToPQB $applyProductSearchQueryParametersToPQB,
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        FindId $getProductId
    ) {
        $this->applyProductSearchQueryParametersToPQB = $applyProductSearchQueryParametersToPQB;
        $this->fromSizePqbFactory = $fromSizePqbFactory;
        $this->searchAfterPqbFactory = $searchAfterPqbFactory;
        $this->getProductId = $getProductId;
    }

    /**
     * @param ListProductsQuery $query
     * @return ProductIdentifierList
     */
    public function handle(ListProductsQuery $query): ProductIdentifierList
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
            /** @phpstan-ignore-next-line */
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

        $result = $pqb->execute();
        $identifiers = array_map(function (IdentifierResult $identifier) {
            return $identifier->getIdentifier();
        }, iterator_to_array($result));

        return new ProductIdentifierList($result->count(), $identifiers);
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
            $id = $this->getProductId->fromIdentifier($query->searchAfter);
            $pqbOptions['search_after_unique_key'] = null === $id ? '' : \sprintf('product_%s', $id);
            $pqbOptions['search_after'] = [\strtolower($query->searchAfter)];
        }

        return $this->searchAfterPqbFactory->create($pqbOptions);
    }
}
