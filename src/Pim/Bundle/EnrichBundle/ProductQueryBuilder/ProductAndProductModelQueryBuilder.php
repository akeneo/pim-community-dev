<?php

namespace Pim\Bundle\EnrichBundle\ProductQueryBuilder;

use Pim\Component\Catalog\Model\ProductInterface;
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
 * they contain (we use the 'attributes_of_ancestors' and 'categories_of_ancestors' properties to achieve it).
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelQueryBuilder implements ProductQueryBuilderInterface
{
    /** @var ProductQueryBuilderInterface */
    private $pqb;

    /** @var ProductAndProductModelSearchAggregator */
    private $searchAggregator;

    /**
     * @param ProductQueryBuilderInterface                $pqb
     * @param ProductAndProductModelSearchAggregator|null $searchAggregator
     *
     * @todo @merge - remove null
     */
    public function __construct(
        ProductQueryBuilderInterface $pqb,
        ProductAndProductModelSearchAggregator $searchAggregator = null
    ) {
        $this->pqb = $pqb;
        $this->searchAggregator = $searchAggregator;
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
        if ($this->shouldFilterOnlyOnProducts()) {
            $this->addFilter('entity_type', Operators::EQUALS, ProductInterface::class);

            return $this->pqb->execute();
        }

        if ($this->shouldSearchDocumentsWithoutParent()) {
            $this->addFilter('parent', Operators::IS_EMPTY, null);
        }

        /** @todo @merge remove null != $this->searchAggregator on master */
        if (!$this->hasRawFilter('field', 'parent') && null !== $this->searchAggregator) {
            $this->searchAggregator->aggregateResults($this->getQueryBuilder(), $this->getRawFilters());
        }

        return $this->pqb->execute();
    }

    /**
     * Should we only filter on lower level products
     *
     * @return bool
     */
    private function shouldFilterOnlyOnProducts(): bool
    {
        $hasStatusFilter = $this->hasRawFilter('field', 'enabled');

        return $hasStatusFilter;
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
        $hasGroupsFilter = $this->hasRawFilter('field', 'groups');
        $hasCategoryFilter = $this->hasFilterOnCategoryWhichImplyAggregation();
        $hasLabelOrIdentifierFilter = $this->hasRawFilter('field', 'label_or_identifier');
        

        return !$hasAttributeFilters &&
            !$hasParentFilter &&
            !$hasIdFilter &&
            !$hasIdentifierFilter &&
            !$hasEntityTypeFilter &&
            !$hasAncestorsIdsFilter &&
            !$hasSelfAndAncestorsIdsFilter &&
            !$hasGroupsFilter &&
            !$hasCategoryFilter &&
            !$hasLabelOrIdentifierFilter;
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

    /**
     * The PQB should aggregate the results only if the operator used is IS_EMPTY or IS_NOT_EMPTY.
     *
     * Only those operators indicate a user selection.
     */
    private function hasFilterOnCategoryWhichImplyAggregation(): bool
    {
        $hasFilter = !empty(array_filter(
            $this->getRawFilters(),
            function (array $filter) {
                return 'field' === $filter['type'] &&
                    'categories' === $filter['field'] &&
                    (Operators::IN_LIST === $filter['operator'] || Operators::IN_CHILDREN_LIST === $filter['operator']);
            }
        ));

        return $hasFilter;
    }
}
