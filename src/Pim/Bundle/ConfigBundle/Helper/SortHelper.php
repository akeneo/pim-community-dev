<?php

namespace Pim\Bundle\ConfigBundle\Helper;

/**
 * Sort helper defines a set of static methods to reorder your arrays
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SortHelper
{
    /**
     * Sort ascending an array of objects using a public property
     *
     * @param array $values    Array of objects
     * @param string $property The property on which sorts (must be public)
     *
     * @return array The sorted array
     *
     * @static
     */
    public static function sortByProperty(array $values, $property)
    {
        uasort(
            $values,
            function ($a, $b) use ($property) {
                if ($a->$property === $b->$property) {
                    return 0;
                }

                return ($a->$property < $b->$property) ? -1 : 1;
            }
        );

        return $values;
    }

    /**
     * Sort ascending an array of values
     *
     * @param array $values Array of values to sort
     *
     * @return array The sorted array
     *
     * @static
     */
    public static function sort(array $values)
    {
        asort($values);

        return $values;
    }
}
