<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\Flash\Message;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ReplaceProductUpdatedFlashMessageSubscriberSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        ObjectRepository $repository,
        FilterResponseEvent $event,
        Request $request,
        ParameterBag $attributes,
        Session $session,
        FlashBagInterface $flashBag,
        Message $noticeFoo,
        Message $noticeBar,
        Message $successFoo
    ) {
        $this->beConstructedWith($authorizationChecker, $repository);

        $event->getRequest()->willReturn($request);
        $request->getSession()->willReturn($session);
        $request->attributes = $attributes;
        $session->getFlashBag()->willReturn($flashBag);

        $noticeFoo->getTemplate()->willReturn('flash.product.updated');
        $noticeBar->getTemplate()->willReturn('flash.notice.bar');
        $successFoo->getTemplate()->willReturn('flash.success.foo');
        $noticeFoo->getParameters()->willReturn([]);
        $noticeBar->getParameters()->willReturn([]);
        $successFoo->getParameters()->willReturn([]);

        $flashBag->peekAll()->willReturn([
            'notice' => [$noticeFoo, $noticeBar],
            'success' => [$successFoo],
        ]);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_the_response_kernel_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            KernelEvents::RESPONSE => ['replaceFlash', 129]
        ]);
    }

    function it_replaces_product_updated_flash_message(
        $authorizationChecker,
        $repository,
        $event,
        $attributes,
        $noticeFoo,
        $noticeBar,
        $successFoo,
        ProductInterface $product
    ) {
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);
        $attributes->get('id')->willReturn('1337');
        $repository->find('1337')->willReturn($product);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);

        $noticeFoo->setTemplate('flash.product_draft.updated')->shouldBeCalled();
        $noticeBar->setTemplate(Argument::any())->shouldNotBeCalled();
        $successFoo->setTemplate(Argument::any())->shouldNotBeCalled();

        $this->replaceFlash($event);
    }

    function it_does_not_replace_product_updated_flash_message_when_dealing_with_a_sub_request(
        $event,
        $noticeFoo,
        $noticeBar,
        $successFoo
    ) {
        $event->getRequestType()->willReturn(HttpKernelInterface::SUB_REQUEST);
        $event->getRequest()->shouldNotBeCalled();

        $noticeFoo->setTemplate(Argument::any())->shouldNotBeCalled();
        $noticeBar->setTemplate(Argument::any())->shouldNotBeCalled();
        $successFoo->setTemplate(Argument::any())->shouldNotBeCalled();

        $this->replaceFlash($event);
    }

    function it_does_not_replace_product_updated_flash_message_when_request_attribute_id_is_unavailable(
        $event,
        $attributes,
        $noticeFoo,
        $noticeBar,
        $successFoo
    ) {
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);
        $attributes->get('id')->willReturn(null);

        $noticeFoo->setTemplate(Argument::any())->shouldNotBeCalled();
        $noticeBar->setTemplate(Argument::any())->shouldNotBeCalled();
        $successFoo->setTemplate(Argument::any())->shouldNotBeCalled();

        $this->replaceFlash($event);
    }

    function it_does_not_replace_product_updated_flash_message_when_current_user_is_the_owner_of_the_product(
        $authorizationChecker,
        $repository,
        $event,
        $attributes,
        $noticeFoo,
        $noticeBar,
        $successFoo,
        ProductInterface $product
    ) {
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);
        $attributes->get('id')->willReturn('1337');
        $repository->find('1337')->willReturn($product);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);

        $noticeFoo->setTemplate(Argument::any())->shouldNotBeCalled();
        $noticeBar->setTemplate(Argument::any())->shouldNotBeCalled();
        $successFoo->setTemplate(Argument::any())->shouldNotBeCalled();

        $this->replaceFlash($event);
    }
}
