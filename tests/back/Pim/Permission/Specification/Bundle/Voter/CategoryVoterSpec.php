<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Voter;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Bundle\Voter\CategoryVoter;
use Akeneo\Pim\Permission\Component\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CategoryVoterSpec extends ObjectBehavior
{
    protected $attributes = [Attributes::VIEW_ITEMS, Attributes::EDIT_ITEMS];

    function let(CategoryAccessManager $accessManager, TokenInterface $token)
    {
        $this->beConstructedWith($accessManager, CategoryInterface::class);
    }

    function it_returns_abstain_access_if_non_attribute_group_entity($token)
    {
        $this
            ->vote($token, 'foo', ['bar', 'baz'])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_abstain_access_if_not_supported_entity($token, CategoryVoter $wrongClass)
    {
        $this
            ->vote($token, $wrongClass, [Attributes::VIEW_ITEMS])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_denied_access_if_user_has_no_access(
        $accessManager,
        $token,
        Category $category,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);

        $accessManager->isUserGranted($user, $category, Attributes::VIEW_ITEMS)->willReturn(false);
        $accessManager->isUserGranted($user, $category, Attributes::EDIT_ITEMS)->willReturn(false);

        $this
            ->vote($token, $category, $this->attributes)
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_granted_access_if_user_has_access(
        $accessManager,
        $token,
        Category $category,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);

        $accessManager->isUserGranted($user, $category, Attributes::VIEW_ITEMS)->willReturn(false);
        $accessManager->isUserGranted($user, $category, Attributes::EDIT_ITEMS)->willReturn(true);

        $this
            ->vote($token, $category, $this->attributes)
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }
}
