<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Voter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Permission\Bundle\Voter\AttributeGroupVoter;
use Akeneo\Pim\Permission\Component\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AttributeVoterSpec extends ObjectBehavior
{
    function let(AttributeGroupVoter $voter)
    {
        $this->beConstructedWith($voter);
    }

    function it_returns_abstain_access_if_non_attribute_entity(TokenInterface $token)
    {
        $this
            ->vote($token, 'foo', ['bar', 'baz'])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_attribute_group_decision_on_attribute_group(
        $voter,
        TokenInterface $token,
        AttributeInterface $attribute,
        AttributeGroupInterface $group
    ) {
        $attribute->getGroup()->willReturn($group);
        $voter->vote($token, $group, [Attributes::VIEW_ATTRIBUTES])->willReturn(VoterInterface::ACCESS_GRANTED);

        $this->vote($token, $attribute, [Attributes::VIEW_ATTRIBUTES])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }
}
