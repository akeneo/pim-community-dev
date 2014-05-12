<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;

use PhpSpec\ObjectBehavior;

class AttributeGroupVoterSpec extends ObjectBehavior
{
    protected $attributes = array(AttributeGroupVoter::VIEW_ATTRIBUTES, AttributeGroupVoter::EDIT_ATTRIBUTES);

    function let(AttributeGroupAccessManager $accessManager, TokenInterface $token)
    {
        $this->beConstructedWith($accessManager);
    }

    function it_returns_abstain_access_if_non_attribute_group_entity($token)
    {
        $this
            ->vote($token, 'foo', array('bar', 'baz'))
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_abstain_access_if_not_supported_entity($token, AttributeGroup $attGroup)
    {
        $this
            ->vote($token, $attGroup, array('bar'))
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_denied_access_if_user_has_no_role(
        $accessManager,
        $token,
        AttributeGroup $attGroup
    ) {
        $accessManager->getEditRoles($attGroup)->willReturn(array());
        $accessManager->getViewRoles($attGroup)->willReturn(array());

        $this
            ->vote($token, $attGroup, $this->attributes)
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }
}
