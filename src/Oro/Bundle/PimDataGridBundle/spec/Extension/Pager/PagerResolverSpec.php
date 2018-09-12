<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Extension\Pager;

use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerResolver;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use PhpSpec\ObjectBehavior;

class PagerResolverSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PagerResolver::class);
    }

    function let(PagerInterface $orm, PagerInterface $dummy)
    {
        $this->beConstructedWith($orm, $dummy, ['foo', 'bar']);
    }

    function it_returns_an_orm_pager_for_non_product_grids($orm)
    {
        $this->getPager('baz')->shouldReturn($orm);
    }

    function it_returns_a_dummy_pager_for_product_grids($dummy)
    {
        $this->getPager('foo')->shouldReturn($dummy);
        $this->getPager('bar')->shouldReturn($dummy);
    }
}
