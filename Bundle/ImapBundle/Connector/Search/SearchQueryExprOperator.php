<?php

namespace Oro\Bundle\ImapBundle\Connector\Search;

class SearchQueryExprOperator implements SearchQueryExprInterface
{
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Can be one of 'AND', 'OR', 'NOT', '(', ')'
     *
     * @var
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
