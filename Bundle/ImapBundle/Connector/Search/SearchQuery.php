<?php

namespace Oro\Bundle\ImapBundle\Connector\Search;

/**
 * Represents a search query for uses in SEARCH command of IMAP protocol.
 *
 * Notes: In all search keys that use strings, a message matches the key if
 * the string is a substring of the field. The matching is case-insensitive.
 */
class SearchQuery
{
    /**
     * @var SearchQueryExpr
     */
    private $expr;

    /** @var SearchStringManagerInterface */
    private $searchStringManager;

    /**
     * Creates SearchQuery object.
     */
    public function __construct(SearchStringManagerInterface $searchStringManager)
    {
        $this->searchStringManager = $searchStringManager;
        $this->expr = new SearchQueryExpr();
    }

    /**
     * Creates new empty instance of this class.
     *
     * @return SearchQuery
     */
    public function newInstance()
    {
        $calledClass = get_called_class();

        return new $calledClass($this->searchStringManager);
    }

    /**
     * Gets the expression represents the search query.
     *
     * @return SearchQueryExpr
     */
    public function getExpression()
    {
        return $this->expr;
    }

    /**
     * Adds a word phrase to be searched in all properties.
     *
     * @param string|SearchQuery $value The word phrase
     * @param int $match The match type. One of SearchQueryMatch::* values
     * @throws \InvalidArgumentException
     */
    public function value($value, $match = SearchQueryMatch::DEFAULT_MATCH)
    {
        if ($value instanceof SearchQuery && $value->isComplex() && $match != SearchQueryMatch::DEFAULT_MATCH) {
            throw new \InvalidArgumentException(
                'The match argument can be specified only if the value argument is a string or a simple query.'
            );
        }

        $this->andOperatorIfNeeded();
        $expr = $value instanceof SearchQuery
            ? $value->getExpression()
            : new SearchQueryExprValue($value, $match);
        $this->expr->add($expr);
    }

    /**
     * Adds name/value pair specifying a word phrase and property where it need to be searched.
     *
     * @param string $name The property name
     * @param string|SearchQuery $value The word phrase
     * @param int $match The match type. One of SearchQueryMatch::* values
     * @throws \InvalidArgumentException
     */
    public function item($name, $value, $match = SearchQueryMatch::DEFAULT_MATCH)
    {
        if ($value instanceof SearchQuery && $value->isComplex() && $match != SearchQueryMatch::DEFAULT_MATCH) {
            throw new \InvalidArgumentException(
                'The match argument can be specified only if the value argument is a string or a simple query.'
            );
        }
        if (!$this->searchStringManager->isAcceptableItem($name, $value, $match)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'This combination of arguments are not valid. Name: %s. Value: %s. Match: %d.',
                    $name,
                    is_object($value) ? get_class($value) : $value,
                    $match
                )
            );
        }

        $this->andOperatorIfNeeded();
        $value = $value instanceof SearchQuery
            ? $value->getExpression()
            : $value;
        $this->expr->add(new SearchQueryExprItem($name, $value, $match));
    }

    /**
     * Adds AND operator.
     */
    public function andOperator()
    {
        $this->expr->add(new SearchQueryExprOperator('AND'));
    }

    /**
     * Adds OR operator.
     */
    public function orOperator()
    {
        $this->expr->add(new SearchQueryExprOperator('OR'));
    }

    /**
     * Adds NOT operator.
     */
    public function notOperator()
    {
        $this->andOperatorIfNeeded();
        $this->expr->add(new SearchQueryExprOperator('NOT'));
    }

    /**
     * Adds open parenthesis '('.
     */
    public function openParenthesis()
    {
        $this->andOperatorIfNeeded();
        $this->expr->add(new SearchQueryExprOperator('('));
    }

    /**
     * Adds close parenthesis ')'.
     */
    public function closeParenthesis()
    {
        $this->expr->add(new SearchQueryExprOperator(')'));
    }

    /**
     * Builds a string representation of the search query.
     *
     * @return string
     * @throws \LogicException
     */
    public function convertToSearchString()
    {
        return $this->searchStringManager->buildSearchString($this->expr);
    }

    /**
     * Checks if this query has no any expressions.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->expr->isEmpty();
    }

    /**
     * Checks if this query has more than one expression.
     *
     * @return bool
     */
    public function isComplex()
    {
        return $this->expr->isComplex();
    }

    private function andOperatorIfNeeded()
    {
        $exprItems = $this->expr->getItems();
        $lastIndex = count($exprItems) - 1;
        if ($lastIndex != -1) {
            $lastItem = $exprItems[$lastIndex];
            if (!($lastItem instanceof SearchQueryExprOperator)
                || (($lastItem instanceof SearchQueryExprOperator) && $lastItem->getName() == ')')
            ) {
                $this->andOperator();
            }
        }
    }
}
