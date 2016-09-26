<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DatagridViewFilterSpec extends ObjectBehavior
{
    function let(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenInterface $token
    ) {
        $tokenStorage->getToken()->willReturn($token);

        $this->beConstructedWith($tokenStorage, $authorizationChecker);
    }

    public function it_does_not_filter_a_datagrid_view_if_the_user_is_granted_to_see_this_datagrid_view(
        $authorizationChecker,
        DatagridView $view
    ) {
        $authorizationChecker->isGranted(Attributes::VIEW, $view)->willReturn(true);

        $this->filterObject($view, 'pim.internal_api.datagrid_view.view')->shouldReturn(false);
    }

    public function it_filters_a_datagrid_view_if_the_user_is_not_granted_to_see_this_datagrid_view(
        $authorizationChecker,
        DatagridView $view
    ) {
        $authorizationChecker->isGranted(Attributes::VIEW, $view)->willReturn(false);

        $this->filterObject($view, 'pim.internal_api.datagrid_view.view')->shouldReturn(true);
    }

    function it_fails_if_it_is_not_a_datagrid_view(\StdClass $anOtherObject)
    {
        $this->shouldThrow('\LogicException')->during('filterObject', [$anOtherObject, 'pim.internal_api.datagrid_view.view']);
    }
}
