<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Query;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;

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

    /**
     * @param ProductQueryBuilderFactoryInterface $productAndProductModelQueryBuilderFactory
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     */
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
     * Count all products matching the given filters
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

        $pqb = $this->productQueryBuilderFactory->create(['filters' => $filters]);

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
}
