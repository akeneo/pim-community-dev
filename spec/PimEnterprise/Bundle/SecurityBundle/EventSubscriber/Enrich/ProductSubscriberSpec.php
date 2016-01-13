<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\Event\ProductEvents;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProductSubscriberSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenInterface $token,
        UserInterface $user,
        UserContext $userContext,
        LocaleInterface $locale,
        TokenStorageInterface $tokenStorage
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $userContext->getCurrentLocale()->willReturn($locale);

        $this->beConstructedWith($authorizationChecker, $userContext);
    }

    function it_subscribes_to_pre_edit_product()
    {
        $this->getSubscribedEvents()->shouldReturn([ProductEvents::PRE_EDIT => 'checkEditPermission']);
    }

    function it_checks_edit_permission($authorizationChecker, GenericEvent $event, ProductInterface $product, $locale)
    {
        $event->getSubject()->willReturn($product);
        $authorizationChecker->isGranted(Argument::any(), $product)->willReturn(false);
        $authorizationChecker->isGranted(Argument::any(), $locale)->willReturn(true);

        $this->shouldThrow(new AccessDeniedException())->during('checkEditPermission', [$event]);
    }
}
