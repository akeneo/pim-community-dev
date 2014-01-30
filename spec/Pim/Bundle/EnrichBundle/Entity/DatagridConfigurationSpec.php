<?php

namespace spec\Pim\Bundle\EnrichBundle\Entity;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\UserBundle\Entity\User;

class DatagridConfigurationSpec extends ObjectBehavior
{
    function it_has_a_datagrid_alias()
    {
        $this->setDatagridAlias('foo-grid');
        $this->getDatagridAlias()->shouldReturn('foo-grid');
    }

    function it_has_a_collection_of_ordered_displayed_columns()
    {
        $this->setColumns(['foo', 'bar', 'baz']);
        $this->getColumns()->shouldReturn(['foo', 'bar', 'baz']);
    }

    function it_has_the_columns_order()
    {
        $this->setOrder('foo,bar,baz');

        $this->getOrder()->shouldReturn('foo,bar,baz');
        $this->getColumns()->shouldReturn(['foo', 'bar', 'baz']);
    }

    function it_does_not_have_any_columns_if_order_is_empty()
    {
        $this->setOrder('');
        $this->getColumns()->shouldHaveCount(0);
    }

    function it_has_a_user(User $user)
    {
        $this->setUser($user);
        $this->getUser()->shouldReturn($user);
    }
}
