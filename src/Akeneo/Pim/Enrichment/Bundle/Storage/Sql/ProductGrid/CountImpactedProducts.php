<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;

/**
 * Given a list of PQB filters, determine the number of products within that selection.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountImpactedProducts
{
    /** @var ProductQueryBuilderFactoryInterface */
    private $productAndProductModelQueryBuilderFactory;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    public function __construct(
        ProductQueryBuilderFactoryInterface $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
    ) {
        $this->productAndProductModelQueryBuilderFactory = $productAndProductModelQueryBuilderFactory;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
    }

    /**
     * @param array $filters
     *
     * @return int
     */
    public function count(array $filters): int
    {
        // ALL
        if ($this->areAllRowsSelected($filters)) {
            return $this->countAllProducts($filters);
        }

        // ALL minus SOME
        if ($this->areSomeRowsUnselected($filters)) {
            return
                $this->countAllProducts($filters) -
                $this->countUnSelectedProducts($filters) -
                $this->countProductsInsideUnSelectedProductModels($filters);
        }

        // SOME
        return $this->countSelectedProducts($filters) + $this->countProductsInsideSelectedProductModels($filters);
    }

    /**
     * Count products inside product model rows that have been selected.
     *
     * @param array $filters
     *
     * @return int
     */
    private function countProductsInsideSelectedProductModels(array $filters): int
    {
        $productModelIds = $this->extractProductModelIds($filters);
        $filters = $this->removeIdFilter($filters);

        $pmqb = $this->productAndProductModelQueryBuilderFactory->create(['filters' => $filters]);
        $pmqb->addFilter('entity_type', Operators::EQUALS, ProductInterface::class);
        $pmqb->addFilter('ancestor.id', Operators::IN_LIST, $productModelIds);
        $count = $pmqb->execute()->count();

        return $count;
    }

    /**
     * Count products inside product model rows that have been unselected.
     * The idea is simply to change the "ID NOT IN ..." by "ID IN ...".
     *
     * @param array $filters
     *
     * @return int
     */
    private function countProductsInsideUnSelectedProductModels(array $filters): int
    {
        foreach ($filters as $keyCondition => $condition) {
            if ('id' === $condition['field'] && Operators::NOT_IN_LIST === $condition['operator']) {
                $filters[$keyCondition]['operator'] = Operators::IN_LIST;
            }
        }

        return $this->countProductsInsideSelectedProductModels($filters);
    }

    /**
     * Count product rows that have been selected.
     *
     * @param array $filters
     *
     * @return int
     */
    private function countSelectedProducts(array $filters): int
    {
        $pqb = $this->productAndProductModelQueryBuilderFactory->create(['filters' => $filters]);
        $pqb->addFilter('entity_type', Operators::EQUALS, ProductInterface::class);

        $count = $pqb->execute()->count();

        return $count;
    }

    /**
     * Count product rows that have been unselected.
     * The idea is simply to change the "ID NOT IN ..." by "ID IN ...".
     *
     * @param array $filters
     *
     * @return int
     */
    private function countUnSelectedProducts(array $filters): int
    {
        foreach ($filters as $keyCondition => $condition) {
            if ('id' === $condition['field'] && Operators::NOT_IN_LIST === $condition['operator']) {
                $filters[$keyCondition]['operator'] = Operators::IN_LIST;
            }
        }

        return $this->countSelectedProducts($filters);
    }

    /**
     * Count all products (only variants) matching the given filters
     * (except we remove the ID filter and adapt the completeness filter).
     *
     * @param array $filters
     *
     * @return int
     */
    private function countAllProducts(array $filters): int
    {
        $filters = $this->adaptGridCompletenessFilter($filters);
        $filters = $this->removeIdFilter($filters);

        foreach ($filters as $index => $filter) {
            if ('label_or_identifier' === $filter['field']) {
                $filters[$index]['field'] = 'self_and_ancestor.label_or_identifier';
            }
        }

        $filters[] = ['field' => 'entity_type', 'operator' => '=', 'value' => ProductInterface::class];

        $pqb = $this->productAndProductModelQueryBuilderFactory->create(['filters' => $filters]);

        $attributeCodesWithIsEmptyOperator = $this->getAttributeCodesWithIsEmptyOperator($pqb->getRawFilters());
        if (!empty($attributeCodesWithIsEmptyOperator)) {
            $pqb->getQueryBuilder()->addFilter([
                'bool' => [
                    'should' => [
                        [
                            'terms' => [
                                'attributes_for_this_level' => $attributeCodesWithIsEmptyOperator
                            ],
                        ],
                        [
                            'terms' => [
                                'attributes_of_ancestors' => $attributeCodesWithIsEmptyOperator
                            ],
                        ],
                    ],
                ],
            ]);
        }

        return $pqb->execute()->count();
    }

    /**
     * All rows are selected in the grid when there is no filter related to the ID of the selected rows.
     *
     * @param array $filters
     *
     * @return bool
     */
    private function areAllRowsSelected(array $filters): bool
    {
        foreach ($filters as $condition) {
            if ('id' === $condition['field']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Some rows are unselected in the grid when we receive a "ID NOT IN ..." filter.
     *
     * @param array $filters
     *
     * @return bool
     */
    private function areSomeRowsUnselected(array $filters): bool
    {
        foreach ($filters as $keyCondition => $condition) {
            if ('id' === $condition['field'] && Operators::NOT_IN_LIST === $condition['operator']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove the ID filter from the filters.
     *
     * @param array $filers
     *
     * @return array
     */
    private function removeIdFilter(array $filers): array
    {
        return array_filter($filers, function ($condition) {
            if ('id' === $condition['field']) {
                return false;
            }

            return true;
        });
    }

    /**
     * Extract product models IDs from the filters.
     *
     * @param array $filters
     *
     * @return array
     */
    private function extractProductModelIds(array $filters): array
    {
        $productModelIds = [];
        foreach ($filters as $keyCondition => $condition) {
            if ('id' === $condition['field']) {
                foreach ($condition['value'] as $keyValue => $id) {
                    if (0 === strpos($id, 'product_model_')) {
                        $productModelIds[] = $id;
                    }
                }
            }
        }

        return $productModelIds;
    }

    /**
     * Adapt the grid completeness filter to the regular Product Query Builder completeness filter.
     *
     * @param array $filters
     *
     * @return array
     */
    private function adaptGridCompletenessFilter(array $filters): array
    {
        foreach ($filters as $keyCondition => $condition) {
            if ('completeness' === $condition['field']) {
                if (Operators::AT_LEAST_COMPLETE === $condition['operator']) {
                    $operator = Operators::EQUALS;
                } else {
                    $operator = Operators::LOWER_THAN;
                }

                $filters[$keyCondition]['operator'] = $operator;
                $filters[$keyCondition]['value'] = 100;
            }
        }

        return $filters;
    }

    /**
     * Returns the attribute codes for which there is a filter on with operator IsEmpty
     *
     * @param string[] $rawFilters
     *
     * @return string[]
     */
    private function getAttributeCodesWithIsEmptyOperator(array $rawFilters): array
    {
        $attributeFilters = array_filter(
            $rawFilters,
            function ($filter) {
                $operator = $filter['operator'];

                return
                    'attribute' === $filter['type'] &&
                    (
                        Operators::IS_EMPTY === $operator ||
                        Operators::IS_EMPTY_FOR_CURRENCY === $operator ||
                        Operators::IS_EMPTY_ON_ALL_CURRENCIES === $operator
                    );
            }
        );

        return array_column($attributeFilters, 'field');
    }
}
