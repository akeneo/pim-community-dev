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
     * If there are no filter on the following fields, the request should not try to group the result by product models.
     * - field Id or identifier
     * - on any attributes
     * - on the parent field
     *
     * @return bool
     */
    private function isSearchGroupedByProductModels(): bool
    {
        $attributeFilters = $this->getAttributeFilters();

        $parentFilter = array_filter(
            $this->getRawFilters(),
            function ($filter) {
                return 'parent' === $filter['field'];
            }
        );

        $idFilter = array_filter(
            $this->getRawFilters(),
            function ($filter) {
                return 'id' === $filter['field'];
            }
        );

        $identifierFilter = array_filter(
            $this->getRawFilters(),
            function ($filter) {
                return 'identifier' === $filter['field'];
            }
        );

        return empty($attributeFilters) && empty($parentFilter) && empty($idFilter) && empty($identifierFilter);
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
}
