<?php

namespace Pim\Bundle\EnrichBundle\ProductQueryBuilder;

use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

/**
 * Provides a way to search simply and efficiently product and product models.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelQueryBuilder implements ProductQueryBuilderInterface
{
    /** @var ProductQueryBuilderInterface */
    private $pqb;

    /**
     * @param ProductQueryBuilderInterface $pqb
     */
    public function __construct(ProductQueryBuilderInterface $pqb)
    {
        $this->pqb = $pqb;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter($field, $operator, $value, array $context = [])
    {
        return $this->pqb->addFilter($field, $operator, $value, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function addSorter($field, $direction, array $context = [])
    {
        return $this->pqb->addSorter($field, $direction, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getRawFilters()
    {
        return $this->pqb->getRawFilters();
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilder()
    {
        return $this->pqb->getQueryBuilder();
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        return $this->pqb->setQueryBuilder($queryBuilder);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->isSearchGroupedByProductModels()) {
            $this->addFilter('parent', Operators::IS_EMPTY, null);
        }

        $attributeFilters = $this->getAttributeFilters();
        if (!empty($attributeFilters)) {
            $attributeFilterKeys = array_column($attributeFilters, 'field');
            $this->addFilter('attributes_for_this_level', Operators::IN_LIST, $attributeFilterKeys);
        }

        return $this->pqb->execute();
    }

    /**
     * Returns the filters on the attributes
     *
     * @return array
     */
    private function getAttributeFilters(): array
    {
        $attributeFilters = array_filter(
            $this->getRawFilters(),
            function ($filter) {
                return 'attribute' === $filter['type'];
            }
        );

        return $attributeFilters;
    }

    /**
     * If there are no filter on the following fields, the request should not try to group the result by product models.
     * - field Id or identifier
     * - on any attributes
     * - on the parent field
     * - on the ancestor field
     *
     * @return bool
     */
    private function isSearchGroupedByProductModels(): bool
    {
        $hasAttributeFilters = $this->hasRawFilter('type', 'attribute');
        $hasParentFilter = $this->hasRawFilter('field', 'parent');
        $hasIdFilter = $this->hasRawFilter('field', 'id');
        $hasIdentifierFilter = $this->hasRawFilter('field', 'identifier');
        $hasEntityTypeFilter = $this->hasRawFilter('field', 'entity_type');
        $hasAncestorsIdsFilter = $this->hasRawFilter('field', 'ancestor.id');

        return !$hasAttributeFilters &&
            !$hasParentFilter &&
            !$hasIdFilter &&
            !$hasIdentifierFilter &&
            !$hasEntityTypeFilter &&
            !$hasAncestorsIdsFilter;
    }

    private function hasRawFilter(string $filterProperty, string $value): bool
    {
        return !empty(array_filter(
            $this->getRawFilters(),
            function ($filter) use ($filterProperty, $value) {
                return $value === $filter[$filterProperty];
            }
        ));
    }
}
