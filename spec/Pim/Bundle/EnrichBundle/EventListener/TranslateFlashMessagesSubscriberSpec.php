<?php

namespace spec\Pim\Bundle\EnrichBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\TranslatorInterface;

class TranslateFlashMessagesSubscriberSpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_the_response_kernel_event_with_a_low_priority()
    {
        $this->getSubscribedEvents()->shouldReturn([
            KernelEvents::VIEW => ['translate', 128],
            KernelEvents::RESPONSE => ['translate', 128],
        ]);
    }

    function it_translates_flash_messages(
        $translator,
        FilterResponseEvent $event,
        Request $request,
        Session $session,
        FlashBagInterface $flashBag,
        Message $noticeFoo,
        Message $noticeBar,
        Message $successFoo
    ) {
        $event->getRequest()->willReturn($request);
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);
        $request->hasSession()->willReturn(true);
        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);

        $noticeFoo->getTemplate()->willReturn('flash.notice.foo');
        $noticeBar->getTemplate()->willReturn('flash.notice.bar');
        $successFoo->getTemplate()->willReturn('flash.success.foo');
        $noticeFoo->getParameters()->willReturn([]);
        $noticeBar->getParameters()->willReturn([]);
        $successFoo->getParameters()->willReturn([]);

        $flashBag->all()->willReturn([
            'notice' => [$noticeFoo, $noticeBar],
            'success' => [$successFoo],
        ]);

        $translator->trans(Argument::type('string'), Argument::type('array'))->will(function ($args) {
            return ucfirst(strtr($args[0], ['.' => ' ']));
        });

        $flashBag
            ->setAll([
                'notice' => [
                    'Flash notice foo',
                    'Flash notice bar',
                ],
                'success' => [
                    'Flash success foo',
                ]
            ])
            ->shouldBeCalled();

        $this->translate($event);
    }
}
