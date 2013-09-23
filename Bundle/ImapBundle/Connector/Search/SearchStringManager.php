<?php

namespace Oro\Bundle\ImapBundle\Connector\Search;

/**
 * Implementation of the search string manager for IMAP servers which don't provide any extensions of SEARCH command.
 */
class SearchStringManager extends AbstractSearchStringManager
{
    /**
     * Matches the name of the search query items to the correspond keyword of SEARCH command
     *
     * @var array
     */
    protected static $keywords = array(
        'from' => 'FROM',
        'to' => 'TO',
        'cc' => 'CC',
        'bcc' => 'BCC',
        'participants' => array(
            'TO',
            'CC',
            'BCC'
        ),
        'subject' => 'SUBJECT',
        'body' => 'BODY',
        'sent:before' => 'SENTBEFORE',
        'sent:after' => 'SENTSINCE',
        'received:before' => 'BEFORE',
        'received:after' => 'SINCE'
    );

    /**
     * {@inheritdoc}
     */
    protected function getNameValueDelimiter()
    {
        return ' ';
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
            || ($match === SearchQueryMatch::SUBSTRING_MATCH);
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchString(SearchQueryExpr $searchQueryExpr)
    {
        return $this->processExpr($this->arrangeExpr($searchQueryExpr));
    }

    /**
     * Arranges the given search expression before it can be converted to its string representation
     * As IMAP SEARCH command uses prefixed OR operator in its search expressions we need to prepare
     * our search expression before it will be converted to a string.
     * Examples:
     *    'val1 OR val2' need to be changed to 'OR val1 val2'
     *    'val1 OR val2 OR val3' need to be changed to 'OR val1 OR val2 val3'
     *    'NOT val1 OR val2' need to be changed to 'OR NOT val1 val2'
     *
     * @param SearchQueryExpr $expr The search expression
     * @return SearchQueryExpr
     */
    protected function arrangeExpr(SearchQueryExpr $expr)
    {
        // Make a clone of the expression and find out OR operators
        $result = new SearchQueryExpr;
        $orOperatorPositions = array();
        $i = 0;
        foreach ($expr as $item) {
            if ($item instanceof SearchQueryExprOperator) {
                $result->add($item);
                if ($item->getName() === 'OR') {
                    $orOperatorPositions[] = $i;
                }
            } elseif ($item instanceof SearchQueryExpr) {
                $result->add($this->arrangeExpr($item));
            } else {
                /** @var SearchQueryExprValueBase $item */
                $value = $item->getValue();
                if ($value instanceof SearchQueryExpr) {
                    $item->setValue($this->arrangeExpr($value));
                }
                $result->add($item);
            }
            $i++;
        }

        // Arrange OR operators is any
        foreach ($orOperatorPositions as $orPos) {
            $i = $orPos - 1;
            $parenthesisCounter = 0;
            while ($i >= 0) {
                $item = $result[$i];
                if ($item instanceof SearchQueryExprOperator) {
                    /** @var SearchQueryExprOperator $item */
                    switch ($item->getName()) {
                        case '(':
                            $parenthesisCounter--;
                            break;
                        case ')':
                            $parenthesisCounter++;
                            break;
                    }
                } elseif ($parenthesisCounter === 0) {
                    $this->moveOperator(
                        $result,
                        $orPos,
                        $this->correctNewPositionOfOperatorIfNeeded($result, $i)
                    );
                    break;
                }
                $i--;
            }
            if ($i === -1 && $parenthesisCounter === 0) {
                $this->moveOperator($result, $orPos, 0);
            }
        }

        return $result;
    }

    /**
     * Moves an operator in a search expression
     *
     * @param SearchQueryExpr $expr The search expression
     * @param int $current The current position of the operator
     * @param int $new The position where the operator to be moved
     * @throws \InvalidArgumentException
     */
    protected function moveOperator(SearchQueryExpr $expr, $current, $new)
    {
        if ($new < 0) {
            throw new \InvalidArgumentException('The new position of the operator must be greater than or equal zero.');
        }
        if ($current < $new) {
            throw new \InvalidArgumentException(
                'The current position of the operator must be greater than its new position.'
            );
        }

        $operator = $expr[$current];
        $i = $current;
        while ($i > $new) {
            $expr[$i] = $expr[$i - 1];
            $i--;
        }
        $expr[$new] = $operator;
    }

    /**
     * Corrects new operator position if there are NOT operators before first OR operand
     *
     * @param SearchQueryExpr $expr
     * @param int $pos The position of the first operand
     * @return int
     */
    protected function correctNewPositionOfOperatorIfNeeded(SearchQueryExpr $expr, $pos)
    {
        $i = $pos - 1;
        while ($i >= 0) {
            $item = $expr[$i];
            if ($item instanceof SearchQueryExprOperator) {
                if ($item->getName() !== 'NOT') {
                    break;
                }
            } else {
                break;
            }
            $i--;
        }

        return $i + 1;
    }

    /**
     * Returns a string represents the given date in format required for search query criterion.
     * Example: 21-Jan-2013
     *
     * @param mixed $value
     * @return string
     */
    protected function formatDate($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('j-M-Y');
        }

        return $value;
    }
}
