<?php

namespace Oro\Bundle\FormBundle\Autocomplete;

interface SearchHandlerInterface extends ConverterInterface
{
    /**
     * Gets search results, that includes found items and any additional information.
     *
     * @param  string $query
     * @param  int    $page
     * @param  int    $perPage
     * @return array
     */
    public function search($query, $page, $perPage);

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
