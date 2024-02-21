<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Query\Sql;

use Doctrine\DBAL\Connection;
use Oro\Bundle\PimDataGridBundle\Query\ListAttributesQuery;
use Oro\Bundle\PimDataGridBundle\Query\ListAttributesUseableInProductGrid;
use PhpSpec\ObjectBehavior;

class ListAttributesUseableInProductGridSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection);
    }

    function it_is_a_list_attributes_useable_in_product_grid_query()
    {
        $this->shouldImplement(ListAttributesUseableInProductGrid::class);
    }
}
