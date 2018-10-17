<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\EventListener;

use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use Akeneo\Platform\Bundle\UIBundle\EventListener\AddLocaleListener;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserContextListenerSpec extends ObjectBehavior
{
    function let(
        TokenStorageInterface $tokenStorage,
        AddLocaleListener $listener,
        CatalogContext $catalogContext,
        UserContext $userContext,
        GetResponseEvent $event
    ) {
        $tokenStorage->getToken()->willReturn(true);
        $event->getRequestType()->willReturn(HttpKernel::MASTER_REQUEST);

        $userContext->getCurrentLocaleCode()->willReturn('de_DE');
        $userContext->getUserChannelCode()->willReturn('schmetterling');

        $this->beConstructedWith($tokenStorage, $listener, $catalogContext, $userContext);
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

    function it_does_nothing_if_no_token_is_present_in_the_security_context(
        $tokenStorage,
        $event,
        $listener,
        $catalogContext
    ) {
        $tokenStorage->getToken()->willReturn(null);

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
