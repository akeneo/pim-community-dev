<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\Workflow\Event\ProductDraftEvents;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Provider\OwnerGroupsProvider;
use PimEnterprise\Bundle\WorkflowBundle\Provider\UsersToNotifyProvider;
use Symfony\Component\EventDispatcher\GenericEvent;

class SendForApprovalSubscriberSpec extends ObjectBehavior
{
    function let(
        NotificationManager $notificationManager,
        UserRepositoryInterface $userRepository,
        OwnerGroupsProvider $ownerGroupsProvider,
        UsersToNotifyProvider $usersProvider
    ) {
        $this->beConstructedWith($notificationManager, $userRepository, $ownerGroupsProvider, $usersProvider);
    }

    function it_subscribes_to_approve_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ProductDraftEvents::POST_READY => ['sendNotificationToOwners'],
        ]);
    }

    function it_sends_notification_to_owners_which_want_to_receive_them(
        $notificationManager,
        $userRepository,
        $ownerGroupsProvider,
        $usersProvider,
        GenericEvent $event,
        ProductDraftInterface $productDraft,
        ProductInterface $product,
        UserInterface $owner1,
        UserInterface $owner2,
        UserInterface $owner3,
        UserInterface $author
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

        $this->sendNotificationToOwners($event);

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
        $notificationManager->notify(
            [$owner1, $owner3],
            'pimee_workflow.proposal.to_review',
            'add',
            [
                'route'         => 'pimee_workflow_proposal_index',
                'comment'       => 'comment',
                'messageParams' => [
                    '%product.label%'    => 'Light Saber',
                    '%author.firstname%' => 'Mary',
                    '%author.lastname%'  => 'Chobu'
                ],
                'context'       => [
                    'actionType'       => 'pimee_workflow_product_draft_notification_new_proposal',
                    'showReportButton' => false,
                    'gridParameters'   => http_build_query($gridParams, 'flags_')
                ]
            ]
        )->shouldBeCalled();
    }
}
