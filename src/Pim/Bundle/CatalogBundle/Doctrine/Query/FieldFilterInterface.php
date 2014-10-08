<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

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
     *
     * @return FieldFilterInterface
     */
    public function addFieldFilter($field, $operator, $value);

    /**
     * This filter supports the field
     *
     * @param string $field
     *
     * @return boolean
     */
    public function supportsField($field);
}
