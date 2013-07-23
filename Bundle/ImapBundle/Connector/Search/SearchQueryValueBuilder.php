<?php

namespace Oro\Bundle\ImapBundle\Connector\Search;

class SearchQueryValueBuilder extends AbstractSearchQueryBuilder
{
    public function value($value)
    {
        $this->query->value($value);

        return $this;
    }
}
