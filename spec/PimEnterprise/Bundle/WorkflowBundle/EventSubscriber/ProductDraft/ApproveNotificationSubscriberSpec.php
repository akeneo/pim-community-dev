<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class ApproveNotificationSubscriberSpec extends ObjectBehavior
{
    function let(NotificationManager $notifier, UserContext $context)
    {
        $this->beConstructedWith($notifier, $context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\ApproveNotificationSubscriber');
    }

    function it_subscribes_to_approve_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ProductDraftEvents::POST_APPROVE => ['send', 10],
        ]);
    }

    function it_does_not_send_on_non_object($notifier, GenericEvent $event)
    {
        $event->getSubject()->willReturn(null);
        $notifier->notify(Argument::any())->shouldNotBeCalled();

        $this->send($event);
    }

    function it_does_not_send_on_non_product_draft($notifier, GenericEvent $event)
    {
        $event->getSubject()->willReturn(new \stdClass());
        $notifier->notify(Argument::any())->shouldNotBeCalled();

        $this->send($event);
    }

    function it_does_not_send_on_unknown_user($notifier, ProductDraftInterface $draft, GenericEvent $event)
    {
        $event->getSubject()->willReturn($draft);
        $notifier->notify(Argument::any())->shouldNotBeCalled();

        $this->send($event);
    }

    function it_sends_a_notification(
        $notifier,
        $context,
        GenericEvent $event,
        UserInterface $owner,
        ProductDraftInterface $draft,
        ProductInterface $product,
        ProductValueInterface $identifier
    ) {
        $event->getSubject()->willReturn($draft);
        $event->hasArgument(Argument::any())->willReturn(false);
        $owner->getFirstName()->willReturn('John');
        $owner->getLastName()->willReturn('Doe');
        $context->getUser()->willReturn($owner);
        $draft->getAuthor()->willReturn('author');
        $draft->getProduct()->willReturn($product);
        $draft->getId()->willReturn(42);
        $product->getId()->willReturn(42);
        $product->getIdentifier()->willReturn($identifier);
        $identifier->getData()->willReturn('tshirt');

        $notifier->notify(
            ['author'],
            'pimee_workflow.product_draft.notification.approve',
            'success',
            [
                'route'         => 'pim_enrich_product_edit',
                'routeParams'   => ['id' => 42],
                'messageParams' => ['%product%' => 'tshirt', '%owner%' => 'John Doe'],
                'context'       => [
                    'actionType'       => 'pimee_workflow_product_draft_notification_approve',
                    'showReportButton' => false
                ]
            ]
        )->shouldBeCalled();

        $this->send($event);
    }

    function it_sends_a_notification_based_on_context(
        $notifier,
        $context,
        GenericEvent $event,
        UserInterface $owner,
        ProductDraftInterface $draft,
        ProductInterface $product,
        ProductValueInterface $identifier
    ) {
        $event->getSubject()->willReturn($draft);
        $owner->getFirstName()->willReturn('John');
        $owner->getLastName()->willReturn('Doe');
        $event->hasArgument('comment')->willReturn(true);
        $event->hasArgument('message')->willReturn(true);
        $event->hasArgument('messageParams')->willReturn(true);
        $event->hasArgument('actionType')->willReturn(true);
        $event->getArgument('comment')->willReturn('a comment');
        $event->getArgument('message')->willReturn('a message');
        $event->getArgument('messageParams')->willReturn(['%owner%' => 'Joe Doe', '%attribute%' => 'name']);
        $event->getArgument('actionType')->willReturn('pimee_workflow_product_draft_notification_partial_approve');

        $context->getUser()->willReturn($owner);
        $draft->getAuthor()->willReturn('author');
        $draft->getProduct()->willReturn($product);
        $draft->getChanges()->willReturn(['values' => ['name' => 'new name']]);
        $draft->getId()->willReturn(null);
        $product->getId()->willReturn(42);
        $product->getIdentifier()->willReturn($identifier);
        $identifier->getData()->willReturn('tshirt');

        $notifier->notify(
            ['author'],
            'a message',
            'success',
            [
                'route'         => 'pim_enrich_product_edit',
                'routeParams'   => ['id' => 42],
                'messageParams' => [
                    '%product%'   => 'tshirt',
                    '%owner%'     => 'Joe Doe',
                    '%attribute%' => 'name'
                ],
                'context'       => [
                    'actionType'       => 'pimee_workflow_product_draft_notification_partial_approve',
                    'showReportButton' => false
                ],
                'comment' => 'a comment'
            ]
        )->shouldBeCalled();

        $this->send($event);
    }
}
