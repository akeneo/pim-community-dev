<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query\Filter;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Aims to register filters useable on product query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterRegistryInterface
{
    /**
     * Register the filter
     *
     * @param FilterInterface $filter
     */
    public function register(FilterInterface $filter);

    /**
     * Get the filter (field or attribute)
     *
     * @param string $code     the field or the attribute code
     * @param string $operator supported operator
     */
    public function getFilter(string $code, string $operator): ?\Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterInterface;

    /**
     * Get the field filter
     *
     * @param string $field     the field
     * @param string $operator  supported operator
     */
    public function getFieldFilter(string $field, string $operator): ?FieldFilterInterface;

    /**
     * Get the attribute filter
     *
     * @param AttributeInterface $attribute
     * @param string             $operator  supported operator
     */
    public function getAttributeFilter(AttributeInterface $attribute, string $operator): ?AttributeFilterInterface;

    /**
     * Returns all field filters
     *
     * @return FieldFilterInterface[]
     */
    public function getFieldFilters(): array;

    /**
     * Returns all attribute filters
     *
     * @return AttributeFilterInterface[]
     */
    public function getAttributeFilters(): array;
}
