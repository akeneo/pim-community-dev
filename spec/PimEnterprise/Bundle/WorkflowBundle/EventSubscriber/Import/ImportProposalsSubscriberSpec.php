<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Import;

use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
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
        NotificationManager $notificationManager,
        UserRepositoryInterface $userRepository,
        OwnerGroupsProvider $ownerGroupsProvider,
        UsersToNotifyProvider $usersProvider,
        ObjectRepository $jobRepository

    ) {
        $this->beConstructedWith(
            $notificationManager,
            $userRepository,
            $ownerGroupsProvider,
            $usersProvider,
            $jobRepository
        );
    }

    function it_should_notify_author_and_owners(
        $notificationManager,
        $userRepository,
        $ownerGroupsProvider,
        $usersProvider,
        $jobRepository,
        GenericEvent $event,
        ProductDraftInterface $productDraft,
        ProductInterface $product,
        JobExecutionEvent $jobExecutionEvent,
        UserInterface $author,
        UserInterface $owner,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $event->getSubject()->willReturn($productDraft);
        $productDraft->getProduct()->willReturn($product);
        $productDraft->getAuthor()->willReturn('import_code');
        $jobRepository
            ->findOneBy(['alias' => 'csv_product_proposal_import', 'code' => 'import_code'])
            ->willReturn($jobInstance);
        $ownerGroupsProvider->getOwnerGroupIds($product)->willReturn(['42']);
        $this->saveGroupIdsToNotify($event);

        $jobExecutionEvent->getJobExecution()->willReturn($jobExecution);
        $jobInstance->getAlias()->willReturn('csv_product_proposal_import');
        $jobInstance->getCode()->willReturn('clothing_product_proposal_import');

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
                        'clothing_product_proposal_import'
                    ]
                ]
            ],
        ];
        $parameters = [
            'route'   => 'pimee_workflow_proposal_index',
            'context' => [
                'actionType'       => 'pimee_workflow_import_notification_new_proposals',
                'showReportButton' => false,
                'gridParameters'   => http_build_query($gridParameters, 'flags_')
            ]
        ];

        $notificationManager->notify(
            [$author],
            'pimee_workflow.proposal.generic_import',
            'add',
            $parameters
        )->shouldBeCalled();

        $parameters['messageParams'] = [
            '%author.firstname%' => 'firstname',
            '%author.lastname%'  => 'lastname'
        ];

        $notificationManager->notify(
            [1 => $owner],
            'pimee_workflow.proposal.individual_import',
            'add',
            $parameters
        )->shouldBeCalled();

        $this->notifyUsers($jobExecutionEvent);
    }
}
