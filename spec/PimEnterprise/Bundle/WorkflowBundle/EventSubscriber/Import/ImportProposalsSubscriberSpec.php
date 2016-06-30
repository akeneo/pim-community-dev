<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Import;

use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Provider\OwnerGroupsProvider;
use PimEnterprise\Bundle\WorkflowBundle\Provider\UsersToNotifyProvider;
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
        $jobRepository,
        $notificationFactory,
        GenericEvent $event,
        ProductDraftInterface $productDraft,
        ProductInterface $product,
        JobExecutionEvent $jobExecutionEvent,
        UserInterface $author,
        UserInterface $owner,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        NotificationInterface $notification
    ) {
        $event->getSubject()->willReturn($productDraft);
        $productDraft->getProduct()->willReturn($product);
        $productDraft->getAuthor()->willReturn('import_code');
        $jobRepository
            ->findOneBy(['jobName' => 'csv_product_proposal_import', 'code' => 'import_code'])
            ->willReturn($jobInstance);
        $ownerGroupsProvider->getOwnerGroupIds($product)->willReturn(['42']);
        $this->saveGroupIdsToNotify($event);

        $jobExecutionEvent->getJobExecution()->willReturn($jobExecution);
        $jobInstance->getJobName()->willReturn('csv_product_proposal_import');
        $jobInstance->getCode()->willReturn('csv_clothing_product_proposal_import');

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
                        'csv_clothing_product_proposal_import'
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
