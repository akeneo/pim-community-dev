<?php

namespace spec\Pim\Bundle\EnrichBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\TranslationBundle\EventListener\AddLocaleListener;

class UserContextListenerSpec extends ObjectBehavior
{
    function let(
        SecurityContextInterface $securityContext,
        AddLocaleListener $listener,
        CatalogContext $catalogContext,
        UserContext $userContext,
        GetResponseEvent $event
    ) {
        $securityContext->getToken()->willReturn(true);
        $event->getRequestType()->willReturn(HttpKernel::MASTER_REQUEST);

        $userContext->getCurrentLocaleCode()->willReturn('de_DE');
        $userContext->getUserChannelCode()->willReturn('schmetterling');

        $this->beConstructedWith($securityContext, $listener, $catalogContext, $userContext);
    }

    function it_subscribes_to_kernel_request()
    {
        $this->getSubscribedEvents()->shouldReturn([KernelEvents::REQUEST => 'onKernelRequest']);
    }

    function it_does_nothing_if_request_type_is_not_master_request($event, $listener, $catalogContext)
    {
        $event->getRequestType()->willReturn('foo');

        $listener->setLocale()->shouldNotBeCalled();
        $catalogContext->setLocaleCode()->shouldNotBeCalled();
        $catalogContext->setScopeCode()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_nothing_if_no_token_is_present_in_the_security_context($securityContext, $event, $listener, $catalogContext)
    {
        $securityContext->getToken()->willReturn(null);

        $listener->setLocale()->shouldNotBeCalled();
        $catalogContext->setLocaleCode()->shouldNotBeCalled();
        $catalogContext->setScopeCode()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_configures_product_manager_with_the_locale_and_scope_from_user_context($event, $catalogContext)
    {
        $catalogContext->setLocaleCode('de_DE')->shouldBeCalled();
        $catalogContext->setScopeCode('schmetterling')->shouldBeCalled();

        $this->onKernelRequest($event);
    }

    function it_configures_locale_listener_with_the_locale_from_user_context($event, $listener)
    {
        $listener->setLocale('de_DE')->shouldBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_not_throw_an_exception_if_user_context_does_not_provide_a_locale($event, $userContext)
    {
        $userContext->getCurrentLocaleCode()->willThrow(new \LogicException());

        $this->shouldNotThrow(new \LogicException())->duringOnKernelRequest($event);
    }
}
