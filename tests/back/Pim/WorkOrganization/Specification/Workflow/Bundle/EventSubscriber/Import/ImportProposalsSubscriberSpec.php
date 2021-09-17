<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\Import;

use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Doctrine\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Provider\OwnerGroupsProvider;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Provider\UsersToNotifyProvider;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class ImportProposalsSubscriberSpec extends ObjectBehavior
{
    function let(
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        OwnerGroupsProvider $ownerGroupsProvider,
        UsersToNotifyProvider $usersProvider,
        ObjectRepository $jobRepository,
        SimpleFactoryInterface $notificationFactory
    ) {
        $this->beConstructedWith(
            $notifier,
            $userRepository,
            $ownerGroupsProvider,
            $usersProvider,
            $jobRepository,
            $notificationFactory
        );
    }

    function it_should_notify_author_and_owners(
        $notifier,
        $userRepository,
        $ownerGroupsProvider,
        $usersProvider,
        $notificationFactory,
        GenericEvent $event,
        EntityWithValuesDraftInterface $productDraft,
        ProductInterface $product,
        JobExecutionEvent $jobExecutionEvent,
        UserInterface $author,
        UserInterface $owner,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        NotificationInterface $notification
    ) {
        $event->getSubject()->willReturn($productDraft);
        $productDraft->getEntityWithValue()->willReturn($product);
        $productDraft->getAuthor()->willReturn('mary');
        $ownerGroupsProvider->getOwnerGroupIds($product)->willReturn(['42']);
        $this->saveGroupIdsToNotify($event);

        $jobExecutionEvent->getJobExecution()->willReturn($jobExecution);
        $jobInstance->getJobName()->willReturn('csv_product_proposal_import');

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getUser()->willReturn('mary');

        $userRepository->findOneBy(['username' => 'mary'])->willReturn($author);
        $usersProvider->getUsersToNotify(['42'])->willReturn([$author, $owner]);

        $author->getFirstName()->willReturn('firstname');
        $author->getLastName()->willReturn('lastname');

        $gridParameters = [
            'f' => [
                'author' => [
                    'value' => [
                        'mary'
                    ]
                ]
            ],
        ];

        $notificationFactory->create()->willReturn($notification);
        $notification->setType('add')->willReturn($notification);
        $notification->setMessage('pimee_workflow.proposal.generic_import')->willReturn($notification);
        $notification->setRoute('pimee_workflow_proposal_index')->willReturn($notification);
        $notification->setComment('Nope Mary.')->willReturn($notification);
        $notification->setContext(
            [
                'actionType'       => 'pimee_workflow_import_notification_new_proposals',
                'showReportButton' => false,
                'gridParameters'   => http_build_query($gridParameters, 'flags_')
            ]
        )->willReturn($notification);

        $notifier->notify($notification, [$author])->shouldBeCalled();

        $notification->setMessage('pimee_workflow.proposal.individual_import')->willReturn($notification);
        $notification->setMessageParams(
            [
                '%author.firstname%' => 'firstname',
                '%author.lastname%'  => 'lastname'
            ]
        )->willReturn($notification);
        $notifier->notify($notification, [1 => $owner])->shouldBeCalled();

        $this->notifyUsers($jobExecutionEvent);
    }

    function it_should_notify_author_and_owners_for_product_model_proposal_import(
        $notifier,
        $userRepository,
        $ownerGroupsProvider,
        $usersProvider,
        $notificationFactory,
        GenericEvent $event,
        EntityWithValuesDraftInterface $productDraft,
        ProductInterface $product,
        JobExecutionEvent $jobExecutionEvent,
        UserInterface $author,
        UserInterface $owner,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        NotificationInterface $notification
    ) {
        $event->getSubject()->willReturn($productDraft);
        $productDraft->getEntityWithValue()->willReturn($product);
        $productDraft->getAuthor()->willReturn('mary');
        $ownerGroupsProvider->getOwnerGroupIds($product)->willReturn(['42']);
        $this->saveGroupIdsToNotify($event);

        $jobExecutionEvent->getJobExecution()->willReturn($jobExecution);
        $jobInstance->getJobName()->willReturn('csv_product_model_proposal_import');

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getUser()->willReturn('mary');

        $userRepository->findOneBy(['username' => 'mary'])->willReturn($author);
        $usersProvider->getUsersToNotify(['42'])->willReturn([$author, $owner]);

        $author->getFirstName()->willReturn('firstname');
        $author->getLastName()->willReturn('lastname');

        $gridParameters = [
            'f' => [
                'author' => [
                    'value' => [
                        'mary'
                    ]
                ]
            ],
        ];

        $notificationFactory->create()->willReturn($notification);
        $notification->setType('add')->willReturn($notification);
        $notification->setMessage('pimee_workflow.proposal.generic_import')->willReturn($notification);
        $notification->setRoute('pimee_workflow_proposal_index')->willReturn($notification);
        $notification->setComment('Nope Mary.')->willReturn($notification);
        $notification->setContext(
            [
                'actionType'       => 'pimee_workflow_import_notification_new_proposals',
                'showReportButton' => false,
                'gridParameters'   => http_build_query($gridParameters, 'flags_')
            ]
        )->willReturn($notification);

        $notifier->notify($notification, [$author])->shouldBeCalled();

        $notification->setMessage('pimee_workflow.proposal.individual_import')->willReturn($notification);
        $notification->setMessageParams(
            [
                '%author.firstname%' => 'firstname',
                '%author.lastname%'  => 'lastname'
            ]
        )->willReturn($notification);
        $notifier->notify($notification, [1 => $owner])->shouldBeCalled();

        $this->notifyUsers($jobExecutionEvent);
    }
}
