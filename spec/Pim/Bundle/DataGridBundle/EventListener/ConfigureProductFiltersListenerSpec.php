<?php

namespace spec\Pim\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;

class ConfigureProductFiltersListenerSpec extends ObjectBehavior
{
    function let(UserContext $context)
    {
        $this->beConstructedWith($context);
    }

    function it_does_not_apply_when_user_preference_is_null($context, UserInterface $user, BuildAfter $event)
    {
        $user->getProductGridFilters()->willReturn(null);
        $context->getUser()->willReturn($user);
        $event->getDatagrid()->shouldNotBeCalled();

        $this->onBuildAfter($event);
    }

    function it_does_not_apply_when_user_preference_is_empty($context, UserInterface $user, BuildAfter $event)
    {
        $user->getProductGridFilters()->willReturn([]);
        $context->getUser()->willReturn($user);
        $event->getDatagrid()->shouldNotBeCalled();

        $this->onBuildAfter($event);
    }

    function it_applies_when_user_preference_is_filled_and_skip_disallowed(
        $context,
        UserInterface $user,
        DatagridInterface $datagrid,
        Acceptor $acceptor,
        DatagridConfiguration $config,
        BuildAfter $event
    ) {
        $config->offsetGet('filters')->willReturn(['columns' => [
            'foo'    => [],
            'baz'    => [],
            'scope'  => [],
            'locale' => [],
        ]]);

        $config->offsetSetByPath('[filters][columns][foo][enabled]', true)->shouldBeCalled();
        $config->offsetSetByPath('[filters][columns][baz][enabled]', false)->shouldBeCalled();
        $config->offsetSetByPath('[filters][columns][bar][enabled]', Argument::any())->shouldNotBeCalled();
        $config->offsetSetByPath('[filters][columns][scope][enabled]', Argument::any())->shouldNotBeCalled();
        $config->offsetSetByPath('[filters][columns][locale][enabled]', Argument::any())->shouldNotBeCalled();

        $user->getProductGridFilters()->willReturn(['foo', 'bar']);
        $context->getUser()->willReturn($user);
        $acceptor->getConfig()->willReturn($config);
        $datagrid->getAcceptor()->willReturn($acceptor);
        $event->getDatagrid()->willReturn($datagrid);

        $this->onBuildAfter($event);
    }
}
