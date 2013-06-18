<?php

namespace Oro\Bundle\FormBundle\Autocomplete;

interface SearchHandlerInterface
{
    /**
     * Gets search results, that includes found items and any additional information.
     *
     * @param string $query
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function search($query, $page, $perPage);

    /**
     * Converts found item into an array that represents it in view.
     *
     * @param mixed $item
     * @return array
     */
    public function convertItem($item);

    /**
     * Gets properties that should be displayed
     *
     * @return array
     */
    public function getProperties();

    /**
     * Gets entity name that is handled by search
     *
     * @return mixed
     */
    public function getEntityName();
}
