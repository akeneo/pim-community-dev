<?php

namespace Oro\Bundle\ImapBundle\Connector\Search;

/**
 * Provides a list of match types use in SearchQuery class
 */
class SearchQueryMatch
{
    /** It is equal to SUBSTRING_MATCH */
    const DEFAULT_MATCH = 0;

    /**
     * Checks the substring exists anywhere in the property value.
     */
    const SUBSTRING_MATCH = 1;

    /**
     * Checks the word exists anywhere in the property value.
     * Examples: "product" matches "product" only, but not "products" or "production".
     * Not all IMAP servers supports this type of the match.
     */
    const EXACT_MATCH = 2;
}
