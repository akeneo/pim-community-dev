<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use Pim\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

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

    function it_returns_denied_access_if_user_has_no_access(
        $accessManager,
        $token,
        CategoryInterface $category,
        User $user
    ) {
        $token->getUser()->willReturn($user);

        $accessManager->isUserGranted($user, $category, Attributes::VIEW_PRODUCTS)->willReturn(false);
        $accessManager->isUserGranted($user, $category, Attributes::EDIT_PRODUCTS)->willReturn(false);

        $this
            ->vote($token, $category, $this->attributes)
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_granted_access_if_user_has_access(
        $accessManager,
        $token,
        CategoryInterface $category,
        User $user
    ) {
        $token->getUser()->willReturn($user);

        $accessManager->isUserGranted($user, $category, Attributes::VIEW_PRODUCTS)->willReturn(false);
        $accessManager->isUserGranted($user, $category, Attributes::EDIT_PRODUCTS)->willReturn(true);

        $this
            ->vote($token, $category, $this->attributes)
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }
}
