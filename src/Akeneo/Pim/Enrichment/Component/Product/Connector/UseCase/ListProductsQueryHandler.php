<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\Api\Security\PrimaryKeyEncrypter;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;

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

    /** @var PrimaryKeyEncrypter */
    private $primaryKeyEncrypter;

    /** @var GetConnectorProducts */
    private $getConnectorProductsQuery;

    public function __construct(
        ApplyProductSearchQueryParametersToPQB $applyProductSearchQueryParametersToPQB,
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        GetConnectorProducts $getConnectorProductsQuery
    ) {
        $this->applyProductSearchQueryParametersToPQB = $applyProductSearchQueryParametersToPQB;
        $this->fromSizePqbFactory = $fromSizePqbFactory;
        $this->searchAfterPqbFactory = $searchAfterPqbFactory;
        $this->primaryKeyEncrypter = $primaryKeyEncrypter;
        $this->getConnectorProductsQuery = $getConnectorProductsQuery;
    }

    /**
     * @throws InvalidQueryException
     */
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

        //$pqb->addFilter('entity_type', Operators::EQUALS, ProductInterface::class);
        $pqb->addSorter('id', Directions::ASCENDING);

        $result = $pqb->execute();
        $identifiers = array_map(function (IdentifierResult $identifier) {
            return $identifier->getIdentifier();
        }, iterator_to_array($result));

        // TODO: inject activated locales if channel is provided (handler responsibility)
        $products = $this->getConnectorProductsQuery->fromProductIdentifiers(
            $identifiers,
            $query->attributeCodes,
            $query->channelCode,
            $query->localeCodes
        );

        return new ConnectorProductList($result->count(), $products);
    }

    private function getSearchPQB(ListProductsQuery $query): ProductQueryBuilderInterface
    {
        if (PaginationTypes::OFFSET === $query->paginationType) {
            return $this->fromSizePqbFactory->create([
                'limit' => (int) $query->limit,
                'from' => ($query->page - 1) * $query->limit
            ]);
        }
        $pqbOptions = ['limit' => (int) $query->limit];

        if (null !== $query->searchAfter) {
            $searchParameterDecrypted = $this->primaryKeyEncrypter->decrypt($query->searchAfter);
            $pqbOptions['search_after_unique_key'] = $searchParameterDecrypted;
            $pqbOptions['search_after'] = [$searchParameterDecrypted];
        }

        return $this->searchAfterPqbFactory->create($pqbOptions);
    }
}
