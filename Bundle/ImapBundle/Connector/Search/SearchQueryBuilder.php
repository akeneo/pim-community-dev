<?php

namespace Oro\Bundle\ImapBundle\Connector\Search;

use \Closure;

class SearchQueryBuilder extends AbstractSearchQueryBuilder
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

    /**
     * Search by FROM field.
     *
     * @param string|Closure $value
     * @param int $match The match type. One of SearchQueryMatch::* values
     * @return $this
     */
    public function from($value, $match = SearchQueryMatch::DEFAULT_MATCH)
    {
        $this->processField('from', $value, $match);
        return $this;
    }

    /**
     * Search by TO field.
     *
     * @param string|Closure $value
     * @param int $match The match type. One of SearchQueryMatch::* values
     * @return $this
     */
    public function to($value, $match = SearchQueryMatch::DEFAULT_MATCH)
    {
        $this->processField('to', $value, $match);
        return $this;
    }

    /**
     * Search by CC field.
     *
     * @param string|Closure $value
     * @param int $match The match type. One of SearchQueryMatch::* values
     * @return $this
     */
    public function cc($value, $match = SearchQueryMatch::DEFAULT_MATCH)
    {
        $this->processField('cc', $value, $match);
        return $this;
    }

    /**
     * Search by BCC field.
     *
     * @param string|Closure $value
     * @param int $match The match type. One of SearchQueryMatch::* values
     * @return $this
     */
    public function bcc($value, $match = SearchQueryMatch::DEFAULT_MATCH)
    {
        $this->processField('bcc', $value, $match);
        return $this;
    }

    /**
     * Search by TO, CC, or BCC fields.
     *
     * @param string|Closure $value
     * @param int $match The match type. One of SearchQueryMatch::* values
     * @return $this
     */
    public function participants($value, $match = SearchQueryMatch::DEFAULT_MATCH)
    {
        $this->processField('participants', $value, $match);
        return $this;
    }

    /**
     * Search by SUBJECT field.
     *
     * @param string|Closure $value
     * @param int $match The match type. One of SearchQueryMatch::* values
     * @return $this
     */
    public function subject($value, $match = SearchQueryMatch::DEFAULT_MATCH)
    {
        $this->processField('subject', $value, $match);
        return $this;
    }

    /**
     * Search by BODY field.
     *
     * @param string|Closure $value
     * @param int $match The match type. One of SearchQueryMatch::* values
     * @return $this
     */
    public function body($value, $match = SearchQueryMatch::DEFAULT_MATCH)
    {
        $this->processField('body', $value, $match);
        return $this;
    }

    /**
     * Search by the attachment file name.
     *
     * @param string|Closure $value
     * @param int $match The match type. One of SearchQueryMatch::* values
     * @return $this
     */
    public function attachment($value, $match = SearchQueryMatch::DEFAULT_MATCH)
    {
        $this->processField('attachment', $value, $match);
        return $this;
    }

    /**
     * Search by SENT field.
     *
     * @param string $fromValue
     * @param string $toValue
     * @return $this
     */
    public function sent($fromValue = null, $toValue = null)
    {
        $this->processDateField('sent', $fromValue, $toValue);
        return $this;
    }

    /**
     * Search by RECEIVED field.
     *
     * @param string $fromValue
     * @param string $toValue
     * @return $this
     */
    public function received($fromValue = null, $toValue = null)
    {
        $this->processDateField('received', $fromValue, $toValue);
        return $this;
    }

    /**
     * Returns a string represents the given date in format required for search query criterion.
     * Example: 21-Jan-2013
     *
     * @param mixed $value
     * @return string
     */
    public function formatDate($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('j-M-Y');
        }

        return $value;
    }

    /**
     * Returns a string represents the given date/time in format required for search query criterion.
     * Example: "02-Jan-2013 23:05:40 +0007"
     *
     * @param mixed $value
     * @return string
     */
    public function formatDateTime($value)
    {
        if ($value instanceof \DateTime) {
            return '"' . $value->format('d-M-Y H:i:s 0') . '"';
        }

        return $value;
    }

    private function processDateField($name, $fromValue = null, $toValue = null)
    {
        if ($fromValue !== null) {
            $this->query->item($name . ':after', $fromValue);
        }
        if ($toValue !== null) {
            $this->query->item($name . ':before', $toValue);
        }
    }

    private function processField($name, $value, $match)
    {
        if ($value instanceof Closure) {
            $exprBuilder = new SearchQueryValueBuilder($this->query->newInstance());
            call_user_func($value, $exprBuilder);
            $value = $exprBuilder->get();
        }
        $this->query->item($name, $value, $match);
    }
}
