<?php

namespace spec\Pim\Bundle\EnrichBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\TranslationBundle\EventListener\AddLocaleListener;

class UserContextListenerSpec extends ObjectBehavior
{
    function let(
        AddLocaleListener $listener,
        ProductManager $productManager,
        UserContext $userContext,
        GetResponseEvent $event
    ) {
        $event->getRequestType()->willReturn(HttpKernel::MASTER_REQUEST);

        $userContext->getCurrentLocaleCode()->willReturn('de_DE');
        $userContext->getUserChannelCode()->willReturn('schmetterling');

        $this->beConstructedWith($listener, $productManager, $userContext);
    }

    function it_subscribes_to_kernel_request()
    {
        $this->getSubscribedEvents()->shouldReturn([KernelEvents::REQUEST => 'onKernelRequest']);
    }

    function it_does_nothing_if_request_type_is_not_master_request($event, $listener, $productManager)
    {
        $event->getRequestType()->willReturn('foo');

        $listener->setLocale()->shouldNotBeCalled();
        $productManager->setLocale()->shouldNotBeCalled();
        $productManager->setScope()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_configures_product_manager_with_the_locale_and_scope_from_user_context($event, $productManager)
    {
        $productManager->setLocale('de_DE')->shouldBeCalled();
        $productManager->setScope('schmetterling')->shouldBeCalled();

        $this->onKernelRequest($event);
    }

    function it_configures_locale_listener_with_the_locale_from_user_context($event, $listener)
    {
        $listener->setLocale('de_DE')->shouldBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_not_throw_an_exception_if_user_context_does_not_provide_a_locale($event, $userContext)
    {
        $userContext->getCurrentLocaleCode()->willThrow(new \Exception());

        $this->onKernelRequest($event);
    }
}
