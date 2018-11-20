<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProductDraft;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Provider\OwnerGroupsProvider;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Provider\UsersToNotifyProvider;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
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
            EntityWithValuesDraftEvents::POST_READY => ['sendNotificationToOwners'],
        ]);
    }

    function it_sends_notification_to_owners_which_want_to_receive_them(
        $notifier,
        $userRepository,
        $ownerGroupsProvider,
        $usersProvider,
        $notificationFactory,
        GenericEvent $event,
        EntityWithValuesDraftInterface $productDraft,
        ProductInterface $product,
        UserInterface $owner1,
        UserInterface $owner2,
        UserInterface $owner3,
        UserInterface $author,
        NotificationInterface $notification,
        LocaleInterface $catalogLocale
    ) {

        $event->getSubject()->willReturn($productDraft);
        $event->getArgument('comment')->willReturn('comment');

        $product->getIdentifier()->willReturn('666');
        $product->getLabel('en_US')->willReturn('Light Saber');

        $productDraft->getEntityWithValue()->willReturn($product);
        $productDraft->getAuthor()->willReturn('mary');

        $catalogLocale->getCode()->willReturn('en_US');

        $author->getFirstName()->willReturn('Mary');
        $author->getLastName()->willReturn('Chobu');
        $author->getUsername()->willReturn('mary');
        $author->getCatalogLocale()->willReturn($catalogLocale);

        $owner1->getProperty('proposals_to_review_notification')->willReturn(true);
        $owner2->getProperty('proposals_to_review_notification')->willReturn(false);
        $owner3->getProperty('proposals_to_review_notification')->willReturn(true);

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
                'identifier'    => [
                    'value' => '666',
                    'type' => 1,
                ],
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
