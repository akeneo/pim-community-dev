<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventListener;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\EnrichBundle\EnrichEvents;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ProductListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\SecurityBundle\EventListener\ProductListener');
    }

    function let(SecurityContextInterface $securityContext, TokenInterface $token, User $user)
    {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($securityContext);
    }

    function it_subscribes_to_pre_edit_product()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [EnrichEvents::PRE_EDIT_PRODUCT => ['checkEditPermission']]
        );
    }

    function it_checks_edit_permission($securityContext, GenericEvent $event, AbstractProduct $product)
    {
        $event->getSubject()->willReturn($product);
        $securityContext->isGranted(Argument::any(), $product)->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('checkEditPermission', [$event]);
    }
}
