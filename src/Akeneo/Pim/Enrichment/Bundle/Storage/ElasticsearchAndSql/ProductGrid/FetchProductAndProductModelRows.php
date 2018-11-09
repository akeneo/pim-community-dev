<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductGrid;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FetchProductAndProductModelRows implements Query\FetchProductAndProductModelRows
{
    /** @var Sql\ProductGrid\FetchProductRowsFromIdentifiers */
    private $fetchProductRowsFromIdentifiers;

    /** @var Sql\ProductGrid\FetchProductModelRowsFromCodes */
    private $fetchProductModelRowsFromCodes;

    /**
     * @param Sql\ProductGrid\FetchProductRowsFromIdentifiers $fetchProductRowsFromIdentifiers
     * @param Sql\ProductGrid\FetchProductModelRowsFromCodes  $fetchProductModelRowsFromCodes
     */
    public function __construct(
        Sql\ProductGrid\FetchProductRowsFromIdentifiers $fetchProductRowsFromIdentifiers,
        Sql\ProductGrid\FetchProductModelRowsFromCodes $fetchProductModelRowsFromCodes
    ) {
        $this->fetchProductRowsFromIdentifiers = $fetchProductRowsFromIdentifiers;
        $this->fetchProductModelRowsFromCodes = $fetchProductModelRowsFromCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Query\FetchProductAndProductModelRowsParameters $queryParameters): Rows
    {
        $productAndProductModelIdentifiersCursor = $queryParameters->productQueryBuilder()->execute();
        $identifiers = iterator_to_array($productAndProductModelIdentifiersCursor);
        $productIdentifiers = [];
        $productModelIdentifiers = [];

        foreach ($identifiers as $identifier) {
            if ($identifier->getType() === ProductInterface::class) {
                $productIdentifiers[] = $identifier->getIdentifier();
            } elseif ($identifier->getType() === ProductModelInterface::class) {
                $productModelIdentifiers[] = $identifier->getIdentifier();
            }
        }

        $productRows = ($this->fetchProductRowsFromIdentifiers)(
            $productIdentifiers,
            $queryParameters->attributeCodes(),
            $queryParameters->channelCode(),
            $queryParameters->localeCode()
        );
        $productModelRows = ($this->fetchProductModelRowsFromCodes)(
            $productModelIdentifiers,
            $queryParameters->attributeCodes(),
            $queryParameters->channelCode(),
            $queryParameters->localeCode()
        );

        $rows = array_merge($productRows, $productModelRows);
        $sortedRows = [];
        foreach ($identifiers as $identifier) {
            foreach ($rows as $row) {
                if ($identifier->getIdentifier() === $row->identifier()) {
                    $sortedRows[] = $row;
                }
            }
        }

        return new Rows($sortedRows, $productAndProductModelIdentifiersCursor->count());
    }
}
