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
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
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
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getAlias()->willReturn('csv_product_proposal_import');
        $jobExecution->getUser()->willReturn('mary');
        $userRepository->findOneBy(['username' => 'mary'])->willReturn($author);
        $usersProvider->getUsersToNotify(['42'])->willReturn([$author, $owner]);

        $author->getFirstName()->willReturn('firstname');
        $author->getLastName()->willReturn('lastname');

        $notificationManager->notify([1 => $owner], Argument::any(), Argument::any(), Argument::any())->willReturn(null);
        $notificationManager->notify([$author], Argument::any(), Argument::any(), Argument::any())->willReturn(null);
        $this->notifyUsers($jobExecutionEvent);
    }
}
