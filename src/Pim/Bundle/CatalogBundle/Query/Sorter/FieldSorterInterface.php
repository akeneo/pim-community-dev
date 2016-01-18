<?php

namespace Pim\Bundle\CatalogBundle\Query\Sorter;

/**
 * Sorter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FieldSorterInterface extends SorterInterface
{
    /**
     * Sort by field
     *
     * @param string $field     the field to sort on
     * @param string $direction the direction to use
     * @param string $locale    the locale
     * @param string $scope     the scope
     *
     * @return FieldSorterInterface
     */
    public function addFieldSorter($field, $direction, $locale = null, $scope = null);

    /**
     * This filter supports the field
     *
     * @param string $field
     *
     * @return bool
     */
    public function supportsField($field);
}
