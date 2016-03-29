<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\Workflow\Event\ProductDraftEvents;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveNotificationSubscriberSpec extends ObjectBehavior
{
    function let(
        NotifierInterface $notifier,
        UserContext $context,
        UserRepositoryInterface $userRepository,
        AttributeRepositoryInterface $attributeRepository,
        SimpleFactoryInterface $notificationFactory
    ) {
        $this->beConstructedWith($notifier, $context, $userRepository, $attributeRepository, $notificationFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\RemoveNotificationSubscriber');
    }

    function it_subscribes_to_approve_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ProductDraftEvents::POST_REMOVE => ['sendNotificationForRemoval', 10],
        ]);
    }

    function it_does_not_send_on_non_object($notifier, GenericEvent $event)
    {
        $event->getSubject()->willReturn(null);
        $notifier->notify(Argument::cetera())->shouldNotBeCalled();

        $this->sendNotificationForRemoval($event);
    }

    function it_does_not_send_on_non_product_draft($notifier, GenericEvent $event)
    {
        $event->getSubject()->willReturn(new \stdClass());
        $notifier->notify(Argument::cetera())->shouldNotBeCalled();

        $this->sendNotificationForRemoval($event);
    }

    function it_does_not_send_on_unknown_user(
        $notifier,
        $userRepository,
        $context,
        ProductDraftInterface $draft,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($draft);
        $draft->getAuthor()->willReturn('author');
        $userRepository->findOneByIdentifier('author')->willReturn(null);
        $notifier->notify(Argument::cetera())->shouldNotBeCalled();
        $context->getUser()->willReturn(null);

        $values = [
            'description' => [['locale' => null, 'scope' => null, 'data' => 'Hi.']]
        ];

        $event->hasArgument('updatedValues')->willReturn(true);
        $event->getArgument('updatedValues')->willReturn($values);
        $event->getArgument('isPartial')->willReturn(false);

        $this->sendNotificationForRemoval($event);
    }

    function it_does_not_send_notification_if_author_does_not_want(
        $userRepository,
        $notifier,
        $context,
        ProductDraftInterface $draft,
        UserInterface $author,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($draft);
        $draft->getAuthor()->willReturn('author');
        $userRepository->findOneByIdentifier('author')->willReturn($author);
        $author->hasProposalsStateNotification()->willReturn(false);

        $notifier->notify(Argument::cetera())->shouldNotBeCalled();
        $context->getUser()->willReturn(null);

        $values = [
            'description' => [['locale' => null, 'scope' => null, 'data' => 'Hi.']]
        ];

        $event->hasArgument('updatedValues')->willReturn(true);
        $event->getArgument('updatedValues')->willReturn($values);
        $event->getArgument('isPartial')->willReturn(false);

        $this->sendNotificationForRemoval($event);
    }

    function it_sends_on_product_draft(
        $notifier,
        $context,
        $userRepository,
        $notificationFactory,
        GenericEvent $event,
        UserInterface $owner,
        UserInterface $author,
        ProductDraftInterface $draft,
        ProductInterface $product,
        NotificationInterface $notification
    ) {
        $context->getCurrentLocaleCode()->willReturn(Argument::any());
        $values = [
            'description' => [['locale' => null, 'scope' => null, 'data' => 'Hi.']]
        ];

        $event->getSubject()->willReturn($draft);
        $event->hasArgument(Argument::any())->willReturn(true);
        $event->hasArgument('comment')->willReturn(false);
        $event->getArgument('message')->willReturn('pimee_workflow.product_draft.notification.approve');
        $event->getArgument('actionType')->willReturn('pimee_workflow_product_draft_notification_approve');
        $event->getArgument('messageParams')->willReturn([]);
        $event->getArgument('updatedValues')->willReturn($values);
        $event->getArgument('isPartial')->willReturn(false);

        $userRepository->findOneByIdentifier('author')->willReturn($author);
        $author->hasProposalsStateNotification()->willReturn(true);

        $owner->getFirstName()->willReturn('John');
        $owner->getLastName()->willReturn('Doe');

        $context->getUser()->willReturn($owner);

        $draft->getAuthor()->willReturn('author');
        $draft->getProduct()->willReturn($product);

        $product->getId()->willReturn(42);
        $product->getLabel(Argument::any())->willReturn('T-Shirt');

        $notificationFactory->create()->willReturn($notification);
        $notification->setType('error')->willReturn($notification);
        $notification->setMessage('pimee_workflow.product_draft.notification.remove')->willReturn($notification);
        $notification->setMessageParams(['%product%' => 'T-Shirt', '%owner%' => 'John Doe'])->willReturn($notification);
        $notification->setRoute('pim_enrich_product_edit')->willReturn($notification);
        $notification->setRouteParams(['id' => 42])->willReturn($notification);
        $notification->setContext(
            [
                'actionType'       => 'pimee_workflow_product_draft_notification_remove',
                'showReportButton' => false
            ]
        )->willReturn($notification);

        $notifier->notify($notification, ['author'])->shouldBeCalled();

        $this->sendNotificationForRemoval($event);
    }

    function it_sends_on_product_draft_with_a_comment(
        $notifier,
        $context,
        $userRepository,
        $notificationFactory,
        GenericEvent $event,
        UserInterface $owner,
        UserInterface $author,
        ProductDraftInterface $draft,
        ProductInterface $product,
        NotificationInterface $notification
    ) {
        $event->getSubject()->willReturn($draft);
        $context->getCurrentLocaleCode()->willReturn(Argument::any());

        $values = [
            'description' => [['locale' => null, 'scope' => null, 'data' => 'Hi.']]
        ];

        $event->hasArgument('updatedValues')->willReturn(true);
        $event->getArgument('updatedValues')->willReturn($values);
        $event->getArgument('isPartial')->willReturn(false);
        $event->hasArgument('comment')->willReturn(true);
        $event->getArgument('comment')->willReturn('Nope Mary.');

        $userRepository->findOneByIdentifier('author')->willReturn($author);
        $author->hasProposalsStateNotification()->willReturn(true);

        $owner->getFirstName()->willReturn('John');
        $owner->getLastName()->willReturn('Doe');

        $context->getUser()->willReturn($owner);

        $draft->getAuthor()->willReturn('author');
        $draft->getProduct()->willReturn($product);

        $product->getId()->willReturn(42);
        $product->getLabel(Argument::any())->willReturn('T-Shirt');

        $notificationFactory->create()->willReturn($notification);
        $notification->setType('error')->willReturn($notification);
        $notification->setMessage('pimee_workflow.product_draft.notification.remove')->willReturn($notification);
        $notification->setMessage('pimee_workflow.product_draft.notification.remove')->willReturn($notification);
        $notification->setMessageParams(['%product%' => 'T-Shirt', '%owner%' => 'John Doe'])->willReturn($notification);
        $notification->setRoute('pim_enrich_product_edit')->willReturn($notification);
        $notification->setRouteParams(['id' => 42])->willReturn($notification);
        $notification->setComment('Nope Mary.')->willReturn($notification);
        $notification->setContext(
            [
                'actionType'       => 'pimee_workflow_product_draft_notification_remove',
                'showReportButton' => false
            ]
        )->willReturn($notification);

        $notifier->notify($notification, ['author'])->shouldBeCalled();

        $this->sendNotificationForRemoval($event);
    }
}
