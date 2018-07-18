<?php

namespace Akeneo\Pim\Enrichment\Bundle\Filter;

/**
 * Collection filter interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CollectionFilterInterface
{
    /**
     * Filter a collection
     *
     * @param mixed  $collection The collection to filter
     * @param string $type       The type of objects in the collection
     * @param array  $options    The filter options
     *
     * @return mixed
     */
    public function filterCollection($collection, $type, array $options = []);

    /**
     * Checks whether the given collection is supported for filtering by this filter
     *
     * @param mixed  $collection The collection to filter
     * @param string $type       The type of objects in the collection
     * @param array  $options    The filter options
     *
     * @return bool
     */
    public function supportsCollection($collection, $type, array $options = []);
}
