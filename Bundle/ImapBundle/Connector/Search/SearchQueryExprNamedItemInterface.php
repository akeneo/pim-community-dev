<?php

namespace Oro\Bundle\ImapBundle\Connector\Search;

interface SearchQueryExprNamedItemInterface extends SearchQueryExprValueInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);
}
