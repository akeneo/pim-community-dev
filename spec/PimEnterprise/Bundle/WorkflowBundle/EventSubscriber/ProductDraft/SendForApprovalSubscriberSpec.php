<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\Workflow\Event\ProductDraftEvents;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Provider\OwnerGroupsProvider;
use PimEnterprise\Bundle\WorkflowBundle\Provider\UsersToNotifyProvider;
use Symfony\Component\EventDispatcher\GenericEvent;

class SendForApprovalSubscriberSpec extends ObjectBehavior
{
    function let(
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        OwnerGroupsProvider $ownerGroupsProvider,
        UsersToNotifyProvider $usersProvider,
        SimpleFactoryInterface $notificationFactory
    ) {
        $this->beConstructedWith($notifier, $userRepository, $ownerGroupsProvider, $usersProvider, $notificationFactory);
    }

    function it_subscribes_to_approve_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ProductDraftEvents::POST_READY => ['sendNotificationToOwners'],
        ]);
    }

    function it_sends_notification_to_owners_which_want_to_receive_them(
        $notifier,
        $userRepository,
        $ownerGroupsProvider,
        $usersProvider,
        $notificationFactory,
        GenericEvent $event,
        ProductDraftInterface $productDraft,
        ProductInterface $product,
        UserInterface $owner1,
        UserInterface $owner2,
        UserInterface $owner3,
        UserInterface $author,
        NotificationInterface $notification
    ) {

        $event->getSubject()->willReturn($productDraft);
        $event->getArgument('comment')->willReturn('comment');

        $product->getId()->willReturn(666);
        $product->getLabel()->willReturn('Light Saber');

        $productDraft->getProduct()->willReturn($product);
        $productDraft->getAuthor()->willReturn('mary');

        $author->getFirstName()->willReturn('Mary');
        $author->getLastName()->willReturn('Chobu');
        $author->getUsername()->willReturn('mary');

        $owner1->hasProposalsToReviewNotification()->willReturn(true);
        $owner2->hasProposalsToReviewNotification()->willReturn(false);
        $owner3->hasProposalsToReviewNotification()->willReturn(true);

        $ownerGroupsProvider->getOwnerGroupIds($product)->willReturn([2, 4]);

        $usersProvider->getUsersToNotify([2, 4])->willReturn([$owner1, $owner3]);
        $userRepository->findOneBy(['username' => 'mary'])->willReturn($author);

        $gridParams = [
            'f' => [
                'author' => [
                    'value' => [
                        'mary'
                    ]
                ],
                'product' => [
                    'value' => [
                        '666'
                    ]
                ]
            ]
        ];

        $notificationFactory->create()->willReturn($notification);
        $notification->setType('add')->willReturn($notification);
        $notification->setComment('comment')->willReturn($notification);
        $notification->setMessage('pimee_workflow.proposal.to_review')->willReturn($notification);
        $notification->setRoute('pimee_workflow_proposal_index')->willReturn($notification);
        $notification->setMessageParams(
            [
                '%product.label%'    => 'Light Saber',
                '%author.firstname%' => 'Mary',
                '%author.lastname%'  => 'Chobu'
            ]
        )->willReturn($notification);
        $notification->setContext(
            [
                'actionType'       => 'pimee_workflow_product_draft_notification_new_proposal',
                'showReportButton' => false,
                'gridParameters'   => http_build_query($gridParams, 'flags_')
            ]
        )->willReturn($notification);

        $notifier->notify($notification, [$owner1, $owner3])->shouldBeCalled();

        $this->sendNotificationToOwners($event);
    }
}
