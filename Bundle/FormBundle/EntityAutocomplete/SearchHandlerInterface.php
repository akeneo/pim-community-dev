<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete;

interface SearchHandlerInterface
{
    /**
     * Search and return results using search string ($search), page number ($page) and page size ($perPage).
     *
     * @param string $search
     * @param int $firstResult
     * @param int $maxResults
     * @return array
     */
    public function search($search, $firstResult, $maxResults);
}
