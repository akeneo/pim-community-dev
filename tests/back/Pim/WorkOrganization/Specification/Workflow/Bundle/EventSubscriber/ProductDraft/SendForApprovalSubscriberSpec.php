<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProductDraft;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Provider\OwnerGroupsProvider;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Provider\UsersToNotifyProvider;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
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
        UserInterface $author,
        NotificationInterface $notification,
        LocaleInterface $catalogLocale
    ) {
        $product = new Product();
        $product->setIdentifier('666');

        $productDraft = new ProductDraft();
        $productDraft->setEntityWithValue($product);
        $productDraft->setAuthor('mary');
        $changes = [
            'title' => [
                ['locale' => null, 'scope' => null, 'values' => 'new', 'status' => ProductDraft::CHANGE_TO_REVIEW],
            ],
            'description' => [
                ['locale' => 'fr_FR', 'scope' => null, 'values' => 'new', 'status' => ProductDraft::CHANGE_TO_REVIEW],
            ],
        ];
        $productDraft->setChanges(['values' => $changes, 'review_statuses' => $changes]);

        $event = new GenericEvent($productDraft, ['comment' => 'comment']);

        $catalogLocale->getCode()->willReturn('en_US');

        $author->getFirstName()->willReturn('Mary');
        $author->getLastName()->willReturn('Chobu');
        $author->getUserIdentifier()->willReturn('mary');
        $author->getCatalogLocale()->willReturn($catalogLocale);

        $owner1 = new User();
        $owner2 = new User();
        $ownerGroupsProvider->getOwnerGroupIds($product)->willReturn([2, 4]);

        // Locales are not filtered because title is not localized.
        $usersProvider->getUsersToNotify([2, 4], ['locales' => null])->willReturn([$owner1, $owner2]);
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
                '%product.label%'    => '666',
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

        $notifier->notify($notification, [$owner1, $owner2])->shouldBeCalled();

        $this->sendNotificationToOwners($event);
    }

    function it_sends_notification_to_owners_filtered_by_locales(
        $notifier,
        $userRepository,
        $ownerGroupsProvider,
        $usersProvider,
        $notificationFactory,
        UserInterface $author,
        NotificationInterface $notification,
        LocaleInterface $catalogLocale
    ) {
        $product = new Product();
        $product->setIdentifier('666');

        $productDraft = new ProductDraft();
        $productDraft->setEntityWithValue($product);
        $productDraft->setAuthor('mary');
        $changes = [
            'title' => [
                ['locale' => 'en_EN', 'scope' => null, 'values' => 'new', 'status' => ProductDraft::CHANGE_TO_REVIEW],
            ],
            'description' => [
                ['locale' => 'fr_FR', 'scope' => null, 'values' => 'new', 'status' => ProductDraft::CHANGE_TO_REVIEW],
            ],
        ];
        $productDraft->setChanges(['values' => $changes, 'review_statuses' => $changes]);

        $event = new GenericEvent($productDraft, ['comment' => 'comment']);

        $catalogLocale->getCode()->willReturn('en_US');

        $author->getFirstName()->willReturn('Mary');
        $author->getLastName()->willReturn('Chobu');
        $author->getUserIdentifier()->willReturn('mary');
        $author->getCatalogLocale()->willReturn($catalogLocale);

        $owner1 = new User();
        $owner2 = new User();
        $ownerGroupsProvider->getOwnerGroupIds($product)->willReturn([2, 4]);

        $usersProvider->getUsersToNotify([2, 4], ['locales' => ['en_EN', 'fr_FR']])->willReturn([$owner1, $owner2]);
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
                '%product.label%'    => '666',
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

        $notifier->notify($notification, [$owner1, $owner2])->shouldBeCalled();

        $this->sendNotificationToOwners($event);
    }
}
