<?php

namespace Oro\Bundle\ImapBundle\Connector\Search;

/**
 * Implementation of the search string manager for X-GM-RAW extension of SEARCH command which is used by gmail.
 */
class GmailSearchStringManager extends AbstractSearchStringManager
{
    /**
     * Matches the name of the search query items to the correspond keyword of SEARCH command
     *
     * @var array
     */
    protected static $keywords = array(
        'from' => 'from',
        'to' => 'to',
        'cc' => 'cc',
        'bcc' => 'bcc',
        'participants' => array(
            'to',
            'cc',
            'bcc'
        ),
        'subject' => 'subject',
        'body' => 'body',
        'attachment' => 'filename',
        'sent:before' => 'before',
        'sent:after' => 'after',
        'received:before' => 'before',
        'received:after' => 'after'
    );

    /**
     * {@inheritdoc}
     */
    protected function getNameValueDelimiter()
    {
        return ':';
    }

    /**
     * {@inheritdoc}
     */
    protected function getKeyword($itemName)
    {
        if (!isset(static::$keywords[$itemName])) {
            return false;
        }

        return static::$keywords[$itemName];
    }

    /**
     * {@inheritdoc}
     */
    public function isAcceptableItem($name, $value, $match)
    {
        if (!isset(static::$keywords[$name])) {
            return false;
        }

        return
            ($match === SearchQueryMatch::DEFAULT_MATCH)
            || ($match === SearchQueryMatch::SUBSTRING_MATCH)
            || ($match === SearchQueryMatch::EXACT_MATCH);
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchString(SearchQueryExpr $searchQueryExpr)
    {
        return sprintf('"%s"', $this->processExpr($searchQueryExpr));
    }

    /**
     * {@inheritdoc}
     */
    protected function processSubQueryValue($itemName, SearchQueryExpr $value)
    {
        if ($value->isEmpty()) {
            return '';
        }

        if ($itemName !== null) {
            $keyword = $this->getKeyword($itemName);
            if (!$keyword) {
                throw new \InvalidArgumentException(sprintf('Unsupported property "%s".', $itemName));
            }
            $result = sprintf('%s%s', $keyword, $this->getNameValueDelimiter());
        } else {
            $result = '';
        }


        $expr = $this->processExpr($value);
        $result .= $value->isComplex()
            ? sprintf('(%s)', $expr)
            : $expr;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeOperator($operator)
    {
        if ($operator === 'NOT') {
            return '-';
        }

        return parent::normalizeOperator($operator);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeValue($keyword, $value, $match)
    {
        $result = parent::normalizeValue($keyword, $value, $match);
        if ($match === SearchQueryMatch::EXACT_MATCH) {
            $result = '+' . $result;
        }

        return str_replace('"', '\\"', $result);
    }

    /**
     * Returns a string represents the given date in format required for search query criterion.
     * Example: 2013/01/21
     *
     * @param mixed $value
     * @return string
     */
    protected function formatDate($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y/m/d');
        }

        return $value;
    }
}
