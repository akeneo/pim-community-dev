<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;
use PimEnterprise\Component\Security\Attributes;
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
        $voter->vote($token, $group, [Attributes::VIEW_ATTRIBUTES])->willReturn('expected vote');

        $this->vote($token, $attribute, [Attributes::VIEW_ATTRIBUTES])->shouldReturn('expected vote');
    }
}
