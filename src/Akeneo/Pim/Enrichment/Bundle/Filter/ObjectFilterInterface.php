<?php

namespace Akeneo\Pim\Enrichment\Bundle\Filter;

/**
 * Object filter interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ObjectFilterInterface
{
    /**
     * Filter an object: if this object should be filtered, this method return true
     *
     * @param mixed  $view    The object to filter
     * @param string $type    The type of objects in the collection
     * @param array  $options The filter options
     *
     * @throws \LogicException If the object is not supported
     *
     * @return bool Does the object should be filtered
     */
    public function filterObject($view, $type, array $options = []);

    /**
     * Checks whether the given object is supported for filtering by this filter
     *
     * @param mixed  $object  The object to filter
     * @param string $type    The type of objects in the collection
     * @param array  $options The filter options
     *
     * @return bool
     */
    public function supportsObject($object, $type, array $options = []);
}
