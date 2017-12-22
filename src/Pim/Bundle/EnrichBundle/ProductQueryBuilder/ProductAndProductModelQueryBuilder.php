<?php

namespace Pim\Bundle\EnrichBundle\ProductQueryBuilder;

use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

/**
 * Provides a way to search product and product models.
 * The results are gathered by the most top level product model matching the search criteria.
 *
 * The most simple use case is that we look for documents without any parent
 * (cf method shouldSearchDocumentsWithoutParent).
 *
 * Otherwise, we have to smartly look for products and product models depending on the values
 * they contain (we add the filter "attributes_for_this_level" in this case).
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
        if ($this->shouldSearchDocumentsWithoutParent()) {
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
     * If there no "particular" filter, that means we want to look for documents that do not have any parent.
     * This happens for instance with the default grid view.
     *
     * @return bool
     */
    private function shouldSearchDocumentsWithoutParent(): bool
    {
        $hasAttributeFilters = $this->hasRawFilter('type', 'attribute');
        $hasParentFilter = $this->hasRawFilter('field', 'parent');
        $hasIdFilter = $this->hasRawFilter('field', 'id');
        $hasIdentifierFilter = $this->hasRawFilter('field', 'identifier');
        $hasEntityTypeFilter = $this->hasRawFilter('field', 'entity_type');
        $hasAncestorsIdsFilter = $this->hasRawFilter('field', 'ancestor.id');
        $hasSelfAndAncestorsIdsFilter = $this->hasRawFilter('field', 'self_and_ancestor.id');

        return !$hasAttributeFilters &&
            !$hasParentFilter &&
            !$hasIdFilter &&
            !$hasIdentifierFilter &&
            !$hasEntityTypeFilter &&
            !$hasAncestorsIdsFilter &&
            !$hasSelfAndAncestorsIdsFilter;
    }

    /**
     * Checks whether the raw filters contains a filter on a particular field.
     *
     * @param string $filterProperty
     * @param string $value
     *
     * @return bool
     */
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
