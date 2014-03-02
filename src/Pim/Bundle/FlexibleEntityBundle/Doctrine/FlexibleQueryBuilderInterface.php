<?php

namespace Pim\Bundle\FlexibleEntityBundle\Doctrine;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Aims to customize a query builder to add useful shortcuts which allow to easily select, filter or sort a flexible
 * entity values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FlexibleQueryBuilderInterface
{
    /**
     * Get locale code
     *
     * @return string
     */
    public function getLocale();

    /**
     * Set locale code
     *
     * @param string $code
     *
     * @return FlexibleQueryBuilderInterface
     */
    public function setLocale($code);

    /**
     * Get scope code
     *
     * @return string
     */
    public function getScope();

    /**
     * Set scope code
     *
     * @param string $code
     *
     * @return FlexibleQueryBuilderInterface
     */
    public function setScope($code);

    /**
     * Add a filter condition on an attribute
     *
     * @param AbstractAttribute $attribute the attribute
     * @param string|array      $operator  the used operator
     * @param string|array      $value     the value(s) to filter
     *
     * @return FlexibleQueryBuilderInterface
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value);

    /**
     * Add a filter condition on a field
     *
     * @param string $field    the field
     * @param string $operator the used operator
     * @param string $value    the value to filter
     *
     * @return FlexibleQueryBuilderInterface
     */
    public function addFieldFilter($field, $operator, $value);

    /**
     * Sort by attribute value
     *
     * @param AbstractAttribute $attribute the attribute to sort on
     * @param string            $direction the direction to use
     *
     * @return FlexibleQueryBuilderInterface
     */
    public function addAttributeSorter(AbstractAttribute $attribute, $direction);

    /**
     * Sort by field
     *
     * @param string $field     the field to sort on
     * @param string $direction the direction to use
     *
     * @return FlexibleQueryBuilderInterface
     */
    public function addFieldSorter($field, $direction);
}
