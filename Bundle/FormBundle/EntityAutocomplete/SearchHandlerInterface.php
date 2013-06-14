<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete;

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
     * Gets entity name that is handled by search
     *
     * @return mixed
     */
    public function getEntityName();
}
