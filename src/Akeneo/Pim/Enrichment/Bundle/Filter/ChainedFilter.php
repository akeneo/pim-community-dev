<?php

namespace Akeneo\Pim\Enrichment\Bundle\Filter;

/**
 * Chained filter: iterate over every filter for a given type and filter the given collection
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedFilter implements CollectionFilterInterface, ObjectFilterInterface
{
    /** @var array */
    protected $collectionFilters = [];

    /** @var array */
    protected $objectFilters = [];

    /**
     * {@inheritdoc}
     */
    public function filterCollection($collection, $type, array $options = [])
    {
        if (isset($this->collectionFilters[$type])) {
            foreach ($this->collectionFilters[$type] as $filter) {
                if ($filter->supportsCollection($collection, $type, $options)) {
                    $collection = $filter->filterCollection($collection, $type, $options);
                }
            }
        }

        if (is_array($collection) && !(array_key_exists('preserve_keys', $options) && $options['preserve_keys'])) {
            $collection = array_values($collection);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCollection($collection, $type, array $options = [])
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function filterObject($view, $type, array $options = [])
    {
        if (isset($this->objectFilters[$type])) {
            foreach ($this->objectFilters[$type] as $filter) {
                if ($filter->supportsObject($view, $type, $options) &&
                    $filter->filterObject($view, $type, $options)
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($collection, $type, array $options = [])
    {
        return false;
    }

    /**
     * Add a filter to the chained collection filter
     *
     * @param CollectionFilterInterface $filter The filter to add
     * @param string                    $type   The filter type
     */
    public function addCollectionFilter(CollectionFilterInterface $filter, $type)
    {
        $this->collectionFilters[$type][] = $filter;
    }

    /**
     * Add a filter to the chained object filter
     *
     * @param ObjectFilterInterface $filter The filter to add
     * @param string                $type   The filter type
     */
    public function addObjectFilter(ObjectFilterInterface $filter, $type)
    {
        $this->objectFilters[$type][] = $filter;
    }
}
