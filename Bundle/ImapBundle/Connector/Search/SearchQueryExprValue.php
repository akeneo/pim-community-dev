<?php

namespace Oro\Bundle\ImapBundle\Connector\Search;

class SearchQueryExprValue extends SearchQueryExprValueBase implements SearchQueryExprValueInterface, SearchQueryExprInterface
{
    /**
     * @param string|SearchQueryExpr $value The word phrase
     * @param int $match The match type. One of SearchQueryMatch::* values
     */
    public function __construct($value, $match)
    {
        parent::__construct($value, $match);
    }
}
