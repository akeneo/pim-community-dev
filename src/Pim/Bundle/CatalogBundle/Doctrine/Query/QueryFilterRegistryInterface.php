<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Aims to register filters useable on product query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface QueryFilterRegistryInterface
{
    /**
     * Register the filter
     *
     * @param FilterInterface $filter
     */
    public function register(FilterInterface $filter);

    /**
     * Get the field filter
     *
     * @param string $field the field
     *
     * @return FilterInterface|null
     */
    public function getFieldFilter($field);

    /**
     * Get the attribute filter
     *
     * @param AttributeInterface $attribute
     *
     * @return FilterInterface|null
     */
    public function getAttributeFilter(AttributeInterface $attribute);
}
