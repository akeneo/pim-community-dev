<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class RefuseNotificationSubscriberSpec extends ObjectBehavior
{
    function let(
        NotificationManager $notifier,
        UserContext $context,
        UserRepositoryInterface $userRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($notifier, $context, $userRepository, $attributeRepository);
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
        $attributeRepository,
        $context,
        ProductDraftInterface $draft,
        GenericEvent $event,
        AttributeInterface $attribute
    ) {
        $event->getSubject()->willReturn($draft);
        $draft->getAuthor()->willReturn('author');
        $userRepository->findOneByIdentifier('author')->willReturn(null);
        $notifier->notify(Argument::cetera())->shouldNotBeCalled();
        $context->getUser()->willReturn(null);

        $event->hasArgument('updatedValues')->willReturn(true);
        $event->getArgument('updatedValues')->willReturn([
            'description' => [['locale' => null, 'scope' => null, 'data' => 'Hi.']]
        ]);
        $draft->getChangesToReview()->willReturn([]);

        $event->setArgument('actionType', Argument::any())->willReturn();
        $event->setArgument('message', Argument::any())->willReturn();
        $event->setArgument('messageParams', Argument::type('array'))->willReturn();

        $context->getCurrentLocaleCode()->willReturn(Argument::any());
        $attribute->getLabel()->willReturn(Argument::any());
        $attribute->setLocale(Argument::any())->shouldBeCalled();
        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn($attribute);

        $this->sendNotificationForRefusal($event);
    }

    function it_does_not_send_if_author_does_not_want_to_receive_notification(
        $userRepository,
        $attributeRepository,
        $notifier,
        $context,
        ProductDraftInterface $draft,
        UserInterface $author,
        GenericEvent $event,
        AttributeInterface $attribute
    ) {
        $event->getSubject()->willReturn($draft);
        $draft->getAuthor()->willReturn('author');
        $userRepository->findOneByIdentifier('author')->willReturn($author);
        $author->hasProposalsStateNotification()->willReturn(false);
        $notifier->notify(Argument::cetera())->shouldNotBeCalled();
        $context->getUser()->willReturn(null);

        $event->hasArgument('updatedValues')->willReturn(true);
        $event->getArgument('updatedValues')->willReturn([
            'description' => [['locale' => null, 'scope' => null, 'data' => 'Hi.']]
        ]);
        $draft->getChangesToReview()->willReturn([]);

        $event->setArgument('actionType', Argument::any())->willReturn();
        $event->setArgument('message', Argument::any())->willReturn();
        $event->setArgument('messageParams', Argument::type('array'))->willReturn();

        $context->getCurrentLocaleCode()->willReturn(Argument::any());
        $attribute->getLabel()->willReturn(Argument::any());
        $attribute->setLocale(Argument::any())->shouldBeCalled();
        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn($attribute);

        $this->sendNotificationForRefusal($event);
    }

    function it_sends_a_notification(
        $notifier,
        $attributeRepository,
        $context,
        $userRepository,
        GenericEvent $event,
        UserInterface $owner,
        UserInterface $author,
        ProductDraftInterface $draft,
        ProductInterface $product,
        ProductValueInterface $identifier,
        AttributeInterface $attribute
    ) {
        $context->getCurrentLocaleCode()->willReturn(Argument::any());

        $event->getSubject()->willReturn($draft);
        $event->hasArgument(Argument::any())->willReturn(true);
        $event->setArgument(Argument::cetera())->willReturn(true);
        $event->hasArgument('comment')->willReturn(false);
        $event->getArgument('message')->willReturn('pimee_workflow.product_draft.notification.refuse');
        $event->getArgument('actionType')->willReturn('pimee_workflow_product_draft_notification_refuse');
        $event->getArgument('messageParams')->willReturn([]);
        $event->getArgument('updatedValues')->willReturn([
            'description' => [['locale' => null, 'scope' => null, 'data' => 'Hi.']]
        ]);

        $attribute->setLocale(Argument::any())->willReturn();
        $attribute->getLabel()->willReturn(Argument::any());
        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn($attribute);

        $draft->getChangesToReview()->willReturn([]);

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

        $notifier->notify(
            ['author'],
            'pimee_workflow.product_draft.notification.refuse',
            'error',
            [
                'route'         => 'pim_enrich_product_edit',
                'routeParams'   => ['id' => 42],
                'messageParams' => ['%product%' => 'T-Shirt', '%owner%' => 'John Doe'],
                'context'       => [
                    'actionType'       => 'pimee_workflow_product_draft_notification_refuse',
                    'showReportButton' => false
                ]
            ]
        )->shouldBeCalled();

        $this->sendNotificationForRefusal($event);
    }

    function it_sends_a_notification_based_on_context(
        $notifier,
        $attributeRepository,
        $context,
        $userRepository,
        GenericEvent $event,
        UserInterface $owner,
        UserInterface $author,
        ProductDraftInterface $draft,
        ProductInterface $product,
        ProductValueInterface $identifier,
        AttributeInterface $attribute
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
        $draft->getChangesToReview()->willReturn([]);
        $event->setArgument(Argument::cetera())->willReturn(true);

        $event->hasArgument('updatedValues')->willReturn(true);
        $event->getArgument('updatedValues')->willReturn([
            'description' => [['locale' => null, 'scope' => null, 'data' => 'Hi.']]
        ]);
        $event->hasArgument('comment')->willReturn(true);
        $event->hasArgument('message')->willReturn(true);
        $event->hasArgument('messageParams')->willReturn(true);
        $event->hasArgument('actionType')->willReturn(true);
        $event->getArgument('comment')->willReturn('a comment');
        $event->getArgument('message')->willReturn('a message');
        $event->getArgument('messageParams')->willReturn(['%owner%' => 'Joe Doe', '%attribute%' => 'name']);
        $event->getArgument('actionType')->willReturn('pimee_workflow_product_draft_notification_partial_reject');

        $context->getUser()->willReturn($owner);

        $draft->getAuthor()->willReturn('author');
        $draft->getProduct()->willReturn($product);

        $product->getId()->willReturn(42);
        $product->getLabel(Argument::any())->willReturn('T-Shirt');

        $identifier->getData()->willReturn('tshirt');

        $notifier->notify(
            ['author'],
            'a message',
            'error',
            [
                'route'         => 'pim_enrich_product_edit',
                'routeParams'   => ['id' => 42],
                'messageParams' => [
                    '%product%'   => 'T-Shirt',
                    '%owner%'     => 'Joe Doe',
                    '%attribute%' => 'name'
                ],
                'context' => [
                    'actionType'       => 'pimee_workflow_product_draft_notification_partial_reject',
                    'showReportButton' => false
                ],
                'comment' => 'a comment'
            ]
        )->shouldBeCalled();

        $this->sendNotificationForRefusal($event);
    }
}
