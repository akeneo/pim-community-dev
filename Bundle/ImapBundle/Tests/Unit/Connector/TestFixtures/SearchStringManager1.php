<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Connector\TestFixtures;

class SearchStringManager1 implements \Oro\Bundle\ImapBundle\Connector\Search\SearchStringManagerInterface
{
    public function isAcceptableItem($name, $value, $match)
    {
        return true;
    }

    public function buildSearchString(\Oro\Bundle\ImapBundle\Connector\Search\SearchQueryExpr $searchQueryExpr)
    {
        return '';
    }
}
