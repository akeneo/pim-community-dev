<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventListener;

use Pim\Bundle\ImportExportBundle\JobEvents;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\UserBundle\Entity\User;

class JobProfileListenerSpec extends ObjectBehavior
{
    function let(SecurityContextInterface $securityContext, TokenInterface $token, User $user)
    {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($securityContext);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\SecurityBundle\EventListener\JobProfileListener');
    }

    function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                JobEvents::PRE_EDIT_JOB_PROFILE => ['checkEditPermission'],
                JobEvents::PRE_EXECUTE_JOB_PROFILE => ['checkExecutePermission']
            ]
        );
    }
}
