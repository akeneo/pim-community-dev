<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

/**
 * Filter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FieldFilterInterface
{
    /**
     * Add an attribute to filter
     *
     * @param string       $field    the field
     * @param string|array $operator the used operator
     * @param string|array $value    the value(s) to filter
     *
     * @return AttributeFilterInterface
     */
    public function addFieldFilter(string $field, $operator, $value);
}
