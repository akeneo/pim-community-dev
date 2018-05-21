<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AttributeGroupVoterSpec extends ObjectBehavior
{
    protected $attributes = [Attributes::VIEW_ATTRIBUTES, Attributes::EDIT_ATTRIBUTES];

    function let(AttributeGroupAccessManager $accessManager, TokenInterface $token)
    {
        $this->beConstructedWith($accessManager);
    }

    function it_returns_abstain_access_if_non_attribute_group_entity($token)
    {
        $this
            ->vote($token, 'foo', ['bar', 'baz'])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_abstain_access_if_not_supported_entity($token, AttributeGroupVoter $wrongClass)
    {
        $this
            ->vote($token, $wrongClass, [Attributes::VIEW_ATTRIBUTES])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_denied_access_if_user_has_no_access(
        $accessManager,
        $token,
        AttributeGroupInterface $attGroup,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);

        $accessManager->isUserGranted($user, $attGroup, Attributes::VIEW_ATTRIBUTES)->willReturn(false);
        $accessManager->isUserGranted($user, $attGroup, Attributes::EDIT_ATTRIBUTES)->willReturn(false);

        $this
            ->vote($token, $attGroup, $this->attributes)
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_granted_access_if_user_has_access(
        $accessManager,
        $token,
        AttributeGroupInterface $attGroup,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);

        $accessManager->isUserGranted($user, $attGroup, Attributes::VIEW_ATTRIBUTES)->willReturn(true);
        $accessManager->isUserGranted($user, $attGroup, Attributes::EDIT_ATTRIBUTES)->willReturn(false);

        $this
            ->vote($token, $attGroup, $this->attributes)
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }
}
