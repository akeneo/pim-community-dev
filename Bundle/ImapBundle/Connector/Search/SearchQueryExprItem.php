<?php

namespace Oro\Bundle\ImapBundle\Connector\Search;

class SearchQueryExprItem extends SearchQueryExprValueBase implements
    SearchQueryExprNamedItemInterface,
    SearchQueryExprValueInterface,
    SearchQueryExprInterface
{
    /**
     * @param string $name The property name
     * @param string|SearchQueryExpr $value The word phrase
     * @param int $match The match type. One of SearchQueryMatch::* values
     */
    public function __construct($name, $value, $match)
    {
        parent::__construct($value, $match);
        $this->name = $name;
    }

    /**
     * The name of a property
     *
     * @var string
     */
    private $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
