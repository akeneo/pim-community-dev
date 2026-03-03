<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductGrid;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet\ProductAndProductsModelDocumentTypeFacetFactory;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FetchProductModelRowsFromCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FetchProductRowsFromUuidsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ResultAwareInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FetchProductAndProductModelRows implements Query\FetchProductAndProductModelRows
{
    public function __construct(
        private FetchProductRowsFromUuidsInterface $fetchProductRowsFromUuids,
        private FetchProductModelRowsFromCodesInterface $fetchProductModelRowsFromCodes,
        private Query\AddAdditionalProductPropertiesRegistry $addAdditionalProductPropertiesRegistry,
        private Query\AddAdditionalProductModelPropertiesRegistry $addAdditionalProductModelPropertiesRegistry,
        private ProductAndProductsModelDocumentTypeFacetFactory $productAndProductsModelDocumentTypeFacetFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Query\FetchProductAndProductModelRowsParameters $queryParameters): Rows
    {
        $productAndProductModelIdentifiersCursor = $queryParameters->productQueryBuilder()->execute();

        $identifiers = iterator_to_array($productAndProductModelIdentifiersCursor);
        $productSearchUuids = [];
        $productModelCodes = [];

        foreach ($identifiers as $identifier) {
            if ($identifier->getType() === ProductInterface::class) {
                $productSearchUuids[] = $identifier->getId();
            } elseif ($identifier->getType() === ProductModelInterface::class) {
                $productModelCodes[] = $identifier->getIdentifier();
            }
        }

        $productRows = ($this->fetchProductRowsFromUuids)(
            $productSearchUuids,
            $queryParameters->attributeCodes(),
            $queryParameters->channelCode(),
            $queryParameters->localeCode()
        );
        $productRows = $this->addAdditionalProductPropertiesRegistry->add($queryParameters, $productRows);

        $productModelRows = ($this->fetchProductModelRowsFromCodes)(
            $productModelCodes,
            $queryParameters->attributeCodes(),
            $queryParameters->channelCode(),
            $queryParameters->localeCode()
        );
        $productModelRows = $this->addAdditionalProductModelPropertiesRegistry->add($queryParameters, $productModelRows);

        $rows = array_merge($productRows, $productModelRows);
        $sortedRows = [];
        foreach ($identifiers as $identifier) {
            foreach ($rows as $row) {
                if ($row->searchId() === $identifier->getId()) {
                    $sortedRows[] = $row;
                }
            }
        }

        $documentTypeFacet = null;
        if ($productAndProductModelIdentifiersCursor instanceof ResultAwareInterface) {
            $documentTypeFacet = $this->productAndProductsModelDocumentTypeFacetFactory->build(
                $productAndProductModelIdentifiersCursor->getResult()
            );
        }

        return new Rows(
            $sortedRows,
            $productAndProductModelIdentifiersCursor->count(),
            $documentTypeFacet !== null ? $documentTypeFacet->getCountForKey(ProductInterface::class) : null,
            $documentTypeFacet !== null ? $documentTypeFacet->getCountForKey(ProductModelInterface::class) : null
        );
    }
}
