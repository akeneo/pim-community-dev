<?php

namespace Pim\Bundle\CatalogBundle\Query\Filter;

/**
 * Filter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FieldFilterInterface extends FilterInterface
{
    /**
     * Add an attribute to filter
     *
     * @param string       $field    the field
     * @param string|array $operator the used operator
     * @param string|array $value    the value(s) to filter
     * @param string       $locale   the locale
     * @param string       $scope    the scope
     * @param array        $options  the filter options
     *
     * @return FieldFilterInterface
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = []);

    /**
     * This filter supports the field
     *
     * @param string $field
     *
     * @return bool
     */
    public function supportsField($field);
}
