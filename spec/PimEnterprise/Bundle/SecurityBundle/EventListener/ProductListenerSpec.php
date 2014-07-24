<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventListener;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\EnrichBundle\EnrichEvents;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\UserBundle\Context\UserContext;

class ProductListenerSpec extends ObjectBehavior
{
    function let(SecurityContextInterface $securityContext, TokenInterface $token, User $user, UserContext $userContext, Locale $locale)
    {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $userContext->getCurrentLocale()->willReturn($locale);

        $this->beConstructedWith($securityContext, $userContext);
    }

    function it_subscribes_to_pre_edit_product()
    {
        $this->getSubscribedEvents()->shouldReturn([
            EnrichEvents::PRE_EDIT_PRODUCT => 'checkEditPermission',
        ]);
    }

    function it_checks_edit_permission($securityContext, GenericEvent $event, AbstractProduct $product, $locale)
    {
        $event->getSubject()->willReturn($product);
        $securityContext->isGranted(Argument::any(), $product)->willReturn(false);
        $securityContext->isGranted(Argument::any(), $locale)->willReturn(true);

        $this->shouldThrow(new AccessDeniedException())->during('checkEditPermission', [$event]);
    }
}
