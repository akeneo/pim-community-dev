<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\Workflow\Event\ProductDraftEvents;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class RefuseNotificationSubscriberSpec extends ObjectBehavior
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
        $this->shouldHaveType('PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\RefuseNotificationSubscriber');
    }

    function it_subscribes_to_refuse_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ProductDraftEvents::POST_REFUSE         => ['sendNotificationForRefusal', 10],
            ProductDraftEvents::POST_PARTIAL_REFUSE => ['sendNotificationForPartialRefusal', 10]
        ]);
    }

    function it_does_not_send_on_non_object($notifier, GenericEvent $event)
    {
        $event->getSubject()->willReturn(null);
        $notifier->notify(Argument::cetera())->shouldNotBeCalled();

        $this->sendNotificationForRefusal($event);
    }

    function it_does_not_send_on_non_product_draft($notifier, GenericEvent $event)
    {
        $event->getSubject()->willReturn(new \stdClass());
        $notifier->notify(Argument::cetera())->shouldNotBeCalled();

        $this->sendNotificationForPartialRefusal($event);
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
        $context->getUser()->willReturn(null);

        $values = [
            'description' => [['locale' => null, 'scope' => null, 'data' => 'Hi.']]
        ];

        $event->hasArgument('updatedValues')->willReturn(true);
        $event->getArgument('updatedValues')->willReturn($values);
        $event->getArgument('isPartial')->willReturn(false);
        $draft->getChangesToReview()->willReturn([]);

        $notifier->notify(Argument::cetera())->shouldNotBeCalled();
        $this->sendNotificationForRefusal($event);
    }

    function it_does_not_send_if_author_does_not_want_to_receive_notification(
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
        $context->getUser()->willReturn(null);

        $values = [
            'description' => [['locale' => null, 'scope' => null, 'data' => 'Hi.']]
        ];

        $event->hasArgument('updatedValues')->willReturn(true);
        $event->getArgument('updatedValues')->willReturn($values);
        $event->getArgument('isPartial')->willReturn(false);
        $draft->getChangesToReview()->willReturn([]);

        $notifier->notify(Argument::cetera())->shouldNotBeCalled();
        $this->sendNotificationForRefusal($event);
    }

    function it_sends_a_notification(
        $notifier,
        $context,
        $userRepository,
        $notificationFactory,
        GenericEvent $event,
        UserInterface $owner,
        UserInterface $author,
        ProductDraftInterface $draft,
        ProductInterface $product,
        ProductValueInterface $identifier,
        NotificationInterface $notification,
        ProductValueInterface $identifier
    ) {
        $context->getCurrentLocaleCode()->willReturn(Argument::any());
        $values = [
            'description' => [['locale' => null, 'scope' => null, 'data' => 'Hi.']]
        ];

        $event->getSubject()->willReturn($draft);
        $event->hasArgument(Argument::any())->willReturn(true);
        $event->hasArgument('comment')->willReturn(false);
        $event->getArgument('message')->willReturn('pimee_workflow.product_draft.notification.refuse');
        $event->getArgument('actionType')->willReturn('pimee_workflow_product_draft_notification_refuse');
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

        $identifier->getData()->willReturn('tshirt');

        $notificationFactory->create()->willReturn($notification);
        $notification->setType('error')->willReturn($notification);
        $notification->setMessage('pimee_workflow.product_draft.notification.refuse')->willReturn($notification);
        $notification->setMessageParams(['%product%' => 'T-Shirt', '%owner%' => 'John Doe'])->willReturn($notification);
        $notification->setRoute('pim_enrich_product_edit')->willReturn($notification);
        $notification->setRouteParams(['id' => 42])->willReturn($notification);
        $notification->setComment('a comment')->willReturn($notification);
        $notification->setContext(
            [
                'actionType'       => 'pimee_workflow_product_draft_notification_refuse',
                'showReportButton' => false
            ]
        )->willReturn($notification);

        $notifier->notify($notification, ['author'])->shouldBeCalled();

        $this->sendNotificationForRefusal($event);
    }

    function it_sends_a_notification_based_on_context(
        $notifier,
        $attributeRepository,
        $context,
        $userRepository,
        $notificationFactory,
        GenericEvent $event,
        UserInterface $owner,
        UserInterface $author,
        ProductDraftInterface $draft,
        ProductInterface $product,
        ProductValueInterface $identifier,
        AttributeInterface $attribute,
        NotificationInterface $notification
    ) {
        $attribute->setLocale(Argument::any())->willReturn();
        $attribute->getLabel()->willReturn(Argument::any());
        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn($attribute);

        $event->getSubject()->willReturn($draft);

        $userRepository->findOneByIdentifier('author')->willReturn($author);
        $author->hasProposalsStateNotification()->willReturn(true);

        $owner->getFirstName()->willReturn('John');
        $owner->getLastName()->willReturn('Doe');

        $context->getCurrentLocaleCode()->willReturn(Argument::any());

        $values = [
            'description' => [['locale' => null, 'scope' => null, 'data' => 'Hi.']]
        ];

        $event->hasArgument('updatedValues')->willReturn(true);
        $event->getArgument('updatedValues')->willReturn($values);
        $event->getArgument('isPartial')->willReturn(false);
        $event->hasArgument('comment')->willReturn(true);
        $event->hasArgument('message')->willReturn(true);
        $event->hasArgument('messageParams')->willReturn(true);
        $event->hasArgument('actionType')->willReturn(true);
        $event->getArgument('comment')->willReturn('a comment');
        $event->getArgument('message')->willReturn('a message');
        $event->getArgument('messageParams')->willReturn(['%owner%' => 'Joe Doe', '%attributes%' => 'Name']);
        $event->getArgument('actionType')->willReturn('pimee_workflow_product_draft_notification_partial_reject');

        $context->getUser()->willReturn($owner);

        $draft->getAuthor()->willReturn('author');
        $draft->getProduct()->willReturn($product);

        $product->getId()->willReturn(42);
        $product->getLabel(Argument::any())->willReturn('T-Shirt');

        $identifier->getData()->willReturn('tshirt');

        $notificationFactory->create()->willReturn($notification);
        $notification->setType('error')->willReturn($notification);
        $notification->setMessage('pimee_workflow.product_draft.notification.refuse')->willReturn($notification);
        $notification->setMessage('a message')->willReturn($notification);
        $notification->setMessageParams(
            [
                '%product%'    => 'T-Shirt',
                '%owner%'      => 'Joe Doe',
                '%attributes%' => 'Name'
            ]
        )->willReturn($notification);
        $notification->setRoute('pim_enrich_product_edit')->willReturn($notification);
        $notification->setRouteParams(['id' => 42])->willReturn($notification);
        $notification->setComment('a comment')->willReturn($notification);
        $notification->setContext(
            [
                'actionType'       => 'pimee_workflow_product_draft_notification_partial_reject',
                'showReportButton' => false
            ]
        )->willReturn($notification);

        $notifier->notify($notification, ['author'])->shouldBeCalled();

        $this->sendNotificationForRefusal($event);
    }
}
