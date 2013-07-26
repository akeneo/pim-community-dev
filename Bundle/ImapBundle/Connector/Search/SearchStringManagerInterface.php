<?php

namespace Oro\Bundle\ImapBundle\Connector\Search;

/**
 * Provides an interface for different kind of search string managers which is used to build a search string
 * which can be passed to an email server through IMAP protocol based on given SearchQuery expression.
 */
interface SearchStringManagerInterface
{
    /**
     * Checks that an item with the given attributes is acceptable for this type of the search string.
     *
     * @param string $name The property name
     * @param string|SearchQueryExpr $value The word phrase or SearchQueryExpr object
     * @param int $match The match type. One of SearchQueryMatch::* values
     */
    public function isAcceptableItem($name, $value, $match);

    /**
     * Builds a string representation of the search query which can be passed to an email server through IMAP protocol.
     *
     * @param SearchQueryExpr $searchQueryExpr
     * @see SearchQuery
     *
     * @return string
     */
    public function buildSearchString(SearchQueryExpr $searchQueryExpr);
}
