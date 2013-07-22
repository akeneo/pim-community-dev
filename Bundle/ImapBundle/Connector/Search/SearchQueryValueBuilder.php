<?php

namespace Oro\Bundle\ImapBundle\Connector\Search;

class SearchQueryValueBuilder extends AbstractSearchQueryBuilder
{
    /**
     * Constructor.
     *
     * @param SearchQuery $query
     */
    public function __construct(SearchQuery $query)
    {
        parent::__construct($query);
    }

    public function value($value)
    {
        $this->query->value($value);
        return $this;
    }
}
