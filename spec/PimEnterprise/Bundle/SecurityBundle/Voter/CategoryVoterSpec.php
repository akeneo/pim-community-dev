<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Oro\Bundle\UserBundle\Entity\User;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Pim\Bundle\CatalogBundle\Entity\Category;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;

class CategoryVoterSpec extends ObjectBehavior
{
    protected $attributes = array(Attributes::VIEW_PRODUCTS, Attributes::EDIT_PRODUCTS);

    function let(CategoryAccessManager $accessManager, TokenInterface $token)
    {
        $this->beConstructedWith($accessManager);
    }

    function it_returns_abstain_access_if_non_attribute_group_entity($token)
    {
        $this
            ->vote($token, 'foo', array('bar', 'baz'))
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_abstain_access_if_not_supported_entity($token, CategoryVoter $wrongClass)
    {
        $this
            ->vote($token, $wrongClass, [Attributes::VIEW_PRODUCTS])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_denied_access_if_user_has_no_role(
        $accessManager,
        $token,
        Category $category
    ) {
        $accessManager->getEditRoles($category)->willReturn(array());
        $accessManager->getViewRoles($category)->willReturn(array());

        $this
            ->vote($token, $category, $this->attributes)
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_denied_access_if_user_has_no_access(
        $accessManager,
        $token,
        Category $category,
        User $user
    ) {
        $token->getUser()->willReturn($user);
        $user->hasRole('foo')->willReturn(false);
        $accessManager->getEditRoles($category)->willReturn(array('foo'));

        $this
            ->vote($token, $category, array(Attributes::EDIT_PRODUCTS))
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_granted_access_if_user_has_access(
        $accessManager,
        $token,
        Category $category,
        User $user
    ) {
        $token->getUser()->willReturn($user);
        $user->hasRole('foo')->willReturn(true);
        $accessManager->getViewRoles($category)->willReturn(array('foo'));

        $this
            ->vote($token, $category, array(Attributes::VIEW_PRODUCTS))
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }
}
