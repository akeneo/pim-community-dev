<?php

namespace spec\Pim\Bundle\DataGridBundle\Query\Sql;

use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Query\ListAttributesQuery;
use Pim\Bundle\DataGridBundle\Query\ListAttributesUseableInProductGrid;

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
