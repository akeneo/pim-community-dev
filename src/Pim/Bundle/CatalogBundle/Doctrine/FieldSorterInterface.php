<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

/**
 * Sorter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FieldSorterInterface
{
    /**
     * Sort by field
     *
     * @param string $field     the field to sort on
     * @param string $direction the direction to use
     *
     * @return FieldSorterInterface
     */
    public function addFieldSorter($field, $direction);
}
