<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\Event\ProductEvents;
use Pim\Bundle\UserBundle\Context\UserContext;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ProductSubscriberSpec extends ObjectBehavior
{
    function let(
        SecurityContextInterface $securityContext,
        TokenInterface $token,
        User $user,
        UserContext $userContext,
        LocaleInterface $locale
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $userContext->getCurrentLocale()->willReturn($locale);

        $this->beConstructedWith($securityContext, $userContext);
    }

    function it_subscribes_to_pre_edit_product()
    {
        $this->getSubscribedEvents()->shouldReturn([ProductEvents::PRE_EDIT => 'checkEditPermission']);
    }

    function it_checks_edit_permission($securityContext, GenericEvent $event, ProductInterface $product, $locale)
    {
        $event->getSubject()->willReturn($product);
        $securityContext->isGranted(Argument::any(), $product)->willReturn(false);
        $securityContext->isGranted(Argument::any(), $locale)->willReturn(true);

        $this->shouldThrow(new AccessDeniedException())->during('checkEditPermission', [$event]);
    }
}
