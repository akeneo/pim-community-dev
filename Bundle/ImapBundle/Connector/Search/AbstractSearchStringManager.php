<?php

namespace Oro\Bundle\ImapBundle\Connector\Search;

/**
 * Implementation of the search string manager for IMAP servers which don't provide any extensions of SEARCH command.
 */
abstract class AbstractSearchStringManager implements SearchStringManagerInterface
{
    /**
     * Gets a string delimits the name and the value of named search items
     *
     * @return string
     */
    abstract protected function getNameValueDelimiter();

    /**
     * Gets a keyword of SEARCH command which corresponds the given item name
     *
     * @param string $itemName The item name
     * @return string|bool a string if the the keyword exists; otherwise, false.
     */
    abstract protected function getKeyword($itemName);

    /**
     * @param SearchQueryExpr $expr The search expression
     * @param string|null $itemName
     * @return string
     */
    protected function processExpr(SearchQueryExpr $expr, $itemName = null)
    {
        $result = '';
        $needWhitespace = false;
        foreach ($expr as $item) {
            if ($item instanceof SearchQueryExprOperator) {
                if ($needWhitespace && $item->getName() !== ')') {
                    $result .= ' ';
                }
                $normalizedOperator = $this->normalizeOperator($item->getName());
                if ($normalizedOperator !== '') {
                    $result .= $normalizedOperator;
                    $needWhitespace = ($item->getName() !== '(');
                } else {
                    $needWhitespace = false;
                }
            } else {
                if ($needWhitespace) {
                    $result .= ' ';
                }
                $result .= $item instanceof SearchQueryExpr
                    ? $this->processSubQueryValue($itemName, $item)
                    : $this->processItem($item, $itemName);
                $needWhitespace = true;
            }
        }

        return $result;
    }

    /**
     * @param SearchQueryExprValueBase $item
     * @param string|null $itemName
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function processItem(SearchQueryExprValueBase $item, $itemName = null)
    {
        if ($itemName === null && $item instanceof SearchQueryExprNamedItemInterface) {
            $itemName = $item->getName();
        }

        $value = $item->getValue();

        return $value instanceof SearchQueryExpr
            ? $this->processSubQueryValue($itemName, $value)
            : $this->processSimpleValue($itemName, $value, $item->getMatch());
    }

    /**
     * @param string $itemName The property name
     * @param SearchQueryExpr $value The sub query
     * @return string
     */
    protected function processSubQueryValue($itemName, SearchQueryExpr $value)
    {
        if ($value->isEmpty()) {
            return '';
        }

        $result = $this->processExpr($value, $itemName);
        if ($value->isComplex()) {
            $result = sprintf('(%s)', $result);
        }

        return $result;
    }

    /**
     * @param string $itemName The property name
     * @param mixed $itemValue A constant the property value is compared
     * @param int $match The match type. One of SearchQueryMatch::* values
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function processSimpleValue($itemName, $itemValue, $match)
    {
        if ($itemName === null) {
            return $this->normalizeValue(null, $itemValue, $match);
        }

        $keyword = $this->getKeyword($itemName);
        if (!$keyword) {
            throw new \InvalidArgumentException(sprintf('Unsupported property "%s".', $itemName));
        }

        return sprintf(
            '%s%s%s',
            $keyword,
            $this->getNameValueDelimiter(),
            $this->normalizeValue($keyword, $itemValue, $match)
        );
    }

    /**
     * @param string $operator A string value contains an operator name to be normalized
     * @return string
     */
    protected function normalizeOperator($operator)
    {
        if ($operator === 'AND') {
            return '';
        }

        return $operator;
    }

    /**
     * @param string $keyword
     * @param mixed $value The value to be normalized
     * @param int $match The match type. One of SearchQueryMatch::* values
     * @return string
     */
    protected function normalizeValue($keyword, $value, $match)
    {
        if ($value instanceof \DateTime) {
            $value = $this->formatDate($value);
        }

        if (is_string($value) && strpos($value, ' ')) {
            return sprintf('"%s"', $value);
        }

        return $value;
    }

    /**
     * Returns a string represents the given date in format required for search query criterion.
     *
     * @param mixed $value
     * @return string
     */
    abstract protected function formatDate($value);
}
