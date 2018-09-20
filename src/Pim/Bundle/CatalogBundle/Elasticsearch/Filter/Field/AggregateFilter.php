<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AggregateFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array                       $supportedFields
     * @param array                       $supportedOperators
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value = null, $locale = null, $channel = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (Operators::AGGREGATE !== $operator) {
            throw InvalidOperatorException::notSupported($operator, static::class);
        }

        if (!isset($options['rawFilters'])) {
            throw InvalidPropertyTypeException::validArrayStructureExpected('aggregate', 'rawFilter missing', static::class, $options);
        }

        $clauses = [];
        $attributeCodes = $this->getAttributeCodes($options['rawFilters']);
        foreach ($attributeCodes as $attributeCode) {
            $clauses[] = [
                'terms' => ['attributes_of_ancestors' => [$attributeCode]],
            ];
        }

        $categoryCodes = $this->getCategoryCodes($options['rawFilters']);
        if (!empty($categoryCodes)) {
            $clauses[] = [
                'terms' => ['categories_of_ancestors' => $categoryCodes],
            ];
        }

        if (!empty($clauses)) {
            $this->searchQueryBuilder->addMustNot(
                [
                    'bool' => [
                        'filter' => $clauses,
                    ],
                ]
            );
        }

        return $this;
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $values
     */
    protected function checkValue($field, $values): void
    {
        FieldFilterHelper::checkArray($field, $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, static::class);
        }
    }

    /**
     * Get children category ids
     *
     * @param integer[] $categoryCodes
     *
     * @return integer[]
     */
    private function getAllChildrenCodes(array $categoryCodes)
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
     * Returns the category codes for which there is a filter on.
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
     * Returns the attribute codes for which there is a filter on.
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
}
