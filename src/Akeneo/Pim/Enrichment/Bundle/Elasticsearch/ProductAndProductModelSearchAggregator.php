<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;

/**
 * Aggregates the results by taking advantage of the raw filters defined on the attributes and the categories.
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductAndProductModelSearchAggregator
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param SearchQueryBuilder $searchQueryBuilder
     * @param array              $rawFilters
     *
     * @return SearchQueryBuilder
     */
    public function aggregateResults(SearchQueryBuilder $searchQueryBuilder, array $rawFilters): SearchQueryBuilder
    {
        $clauses = [];
        $attributeCodes = $this->getAttributeCodes($rawFilters);
        foreach ($attributeCodes as $attributeCode) {
            $clauses[] = [
                'terms' => ['attributes_of_ancestors' => [$attributeCode]],
            ];
        }

        $categoryCodes = $this->getCategoryCodes($rawFilters);
        if (!empty($categoryCodes)) {
            $clauses[] = [
                'terms' => ['categories_of_ancestors' => $categoryCodes],
            ];
        }

        if (!empty($clauses)) {
            $searchQueryBuilder->addMustNot(
                [
                    'bool' => [
                        'filter' => $clauses,
                    ],
                ]
            );
        }

        $attributeCodesWithIsEmptyOperator = $this->getAttributeCodesWithIsEmptyOperator($rawFilters);
        if (!empty($attributeCodesWithIsEmptyOperator)) {
            $searchQueryBuilder->addFilter(
                [
                    'terms' => [
                        'attributes_for_this_level' => $attributeCodesWithIsEmptyOperator,
                    ],
                ]
            );
        }

        return $searchQueryBuilder;
    }

    /**
     * Returns the attribute codes for which there is a filter on.
     *
     * @param string[] $rawFilters
     *
     * @return string[]
     */
    private function getAttributeCodes(array $rawFilters): array
    {
        $attributeFilters = array_filter(
            $rawFilters,
            function ($filter) {
                return 'attribute' === $filter['type'];
            }
        );

        return array_column($attributeFilters, 'field');
    }

    /**
     * Returns the category codes for which there is a filter on.
     *
     * @param string[] $rawFilters
     *
     * @return string[]
     */
    private function getCategoryCodes(array $rawFilters): array
    {
        $categoriesFilter = array_filter(
            $rawFilters,
            function ($filter) {
                return 'field' === $filter['type'] &&
                    'categories' === $filter['field'] &&
                    (Operators::IN_LIST === $filter['operator'] || Operators::IN_CHILDREN_LIST === $filter['operator']);
            }
        );
        $categoryCodes = [];
        foreach ($categoriesFilter as $categoryFilter) {
            $categoryCodes = array_merge($categoryCodes, $categoryFilter['value']);
            if (Operators::IN_CHILDREN_LIST === $categoryFilter['operator']) {
                $childrenCategory = $this->getAllChildrenCodes($categoryCodes);
                $categoryCodes = array_merge($categoryCodes, $childrenCategory);
            }
        }

        return $categoryCodes;
    }

    /**
     * Get children category ids
     *
     * @param integer[] $categoryCodes
     *
     * @return integer[]
     */
    private function getAllChildrenCodes(array $categoryCodes): array
    {
        $allChildrenCodes = [];
        foreach ($categoryCodes as $categoryCode) {
            $category = $this->categoryRepository->findOneBy(['code' => $categoryCode]);
            if (null !== $category) {
                $childrenCodes = $this->categoryRepository->getAllChildrenCodes($category);
                $childrenCodes[] = $category->getCode();
                $allChildrenCodes = array_merge($allChildrenCodes, $childrenCodes);
            }
        }

        return $allChildrenCodes;
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
