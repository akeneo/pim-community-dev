<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete;

interface SearchHandlerInterface
{
    /**
     * Search and return results using search string ($search), page number ($page) and page size ($perPage).
     *
     * @param string $search
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function search($search, $page, $perPage);
}
