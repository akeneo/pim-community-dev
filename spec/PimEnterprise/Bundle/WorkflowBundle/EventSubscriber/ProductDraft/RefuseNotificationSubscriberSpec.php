<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class RefuseNotificationSubscriberSpec extends ObjectBehavior
{
    function let(NotificationManager $notifier, UserContext $context, UserRepositoryInterface $userRepository)
    {
        $this->beConstructedWith($notifier, $context, $userRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\RefuseNotificationSubscriber');
    }

    function it_subscribes_to_approve_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ProductDraftEvents::POST_REFUSE => ['send', 10],
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

    function it_does_not_send_on_unknown_user(
        $notifier,
        $userRepository,
        ProductDraftInterface $draft,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($draft);
        $draft->getAuthor()->willReturn('author');
        $userRepository->findOneByIdentifier('author')->willReturn(null);
        $notifier->notify(Argument::any())->shouldNotBeCalled();

        $this->send($event);
    }

    function it_does_not_send_if_author_does_not_want_to_receive_notification(
        $userRepository,
        $notifier,
        ProductDraftInterface $draft,
        UserInterface $author,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($draft);
        $draft->getAuthor()->willReturn('author');
        $userRepository->findOneByIdentifier('author')->willReturn($author);
        $author->hasProposalsStateNotification()->willReturn(false);

        $notifier->notify(Argument::cetera())->shouldNotBeCalled();

        $this->send($event)->shouldReturn(null);
    }

    function it_sends_on_product_draft(
        $notifier,
        $context,
        $userRepository,
        GenericEvent $event,
        UserInterface $owner,
        UserInterface $author,
        ProductDraftInterface $draft,
        ProductInterface $product,
        ProductValueInterface $identifier
    ) {
        $event->hasArgument('comment')->willReturn(false);
        $event->getSubject()->willReturn($draft);

        $userRepository->findOneByIdentifier('author')->willReturn($author);
        $author->hasProposalsStateNotification()->willReturn(true);

        $owner->getFirstName()->willReturn('John');
        $owner->getLastName()->willReturn('Doe');

        $context->getUser()->willReturn($owner);

        $draft->getAuthor()->willReturn('author');
        $draft->getProduct()->willReturn($product);

        $product->getId()->willReturn(42);
        $product->getIdentifier()->willReturn($identifier);

        $identifier->getData()->willReturn('tshirt');

        $notifier->notify(
            ['author'],
            'pimee_workflow.product_draft.notification.refuse',
            'error',
            [
                'route'         => 'pim_enrich_product_edit',
                'routeParams'   => ['id' => 42],
                'messageParams' => ['%product%' => 'tshirt', '%owner%' => 'John Doe'],
                'context'       => [
                    'actionType'       => 'pimee_workflow_product_draft_notification_refuse',
                    'showReportButton' => false
                ]
            ]
        )->shouldBeCalled();

        $this->send($event);
    }

    function it_sends_on_product_draft_with_a_comment(
        $notifier,
        $context,
        $userRepository,
        GenericEvent $event,
        UserInterface $owner,
        UserInterface $author,
        ProductDraftInterface $draft,
        ProductInterface $product,
        ProductValueInterface $identifier
    ) {
        $event->getSubject()->willReturn($draft);
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
        $product->getIdentifier()->willReturn($identifier);

        $identifier->getData()->willReturn('tshirt');

        $notifier->notify(
            ['author'],
            'pimee_workflow.product_draft.notification.refuse',
            'error',
            [
                'route'         => 'pim_enrich_product_edit',
                'routeParams'   => ['id' => 42],
                'messageParams' => ['%product%' => 'tshirt', '%owner%' => 'John Doe'],
                'context'       => [
                    'actionType' => 'pimee_workflow_product_draft_notification_refuse',
                    'showReportButton' => false,
                ],
                'comment'    => 'Nope Mary.',
            ]
        )->shouldBeCalled();

        $this->send($event);
    }
}
