<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Import;

use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class ImportProposalsSubscriberSpec extends ObjectBehavior
{
    function let(
        NotificationManager $notificationManager,
        CategoryAccessRepository $categoryAccessRepo,
        UserRepositoryInterface $userRepository
    ) {
        $this->beConstructedWith($notificationManager, $categoryAccessRepo, $userRepository);
    }

    function it_should_notify_author_and_owners(
        $notificationManager,
        $categoryAccessRepo,
        $userRepository,
        GenericEvent $event,
        ProductDraftInterface $productDraft,
        ProductInterface $product,
        JobExecutionEvent $jobExecutionEvent,
        UserInterface $author,
        UserInterface $owner,
        JobExecution $jobExecution
    ) {
        $event->getSubject()->willReturn($productDraft);
        $productDraft->getProduct()->willReturn($product);
        $categoryAccessRepo
            ->getGrantedUserGroupsForProduct($product, Attributes::OWN_PRODUCTS)
            ->willReturn([['id' => '42']]);
        $this->saveGroupIdsToNotify($event);

        $jobExecutionEvent->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('mary');
        $userRepository->findOneBy(['username' => 'mary'])->willReturn($author);
        $userRepository->findByGroups(['42'])->willReturn([$author, $owner]);
        $author->hasProposalsToReviewNotification()->willReturn(true);
        $owner->hasProposalsToReviewNotification()->willReturn(true);
        $author->getFirstName()->willReturn('firstname');
        $author->getLastName()->willReturn('lastname');

        $notificationManager->notify([1 => $owner], Argument::any(), Argument::any(), Argument::any())->willReturn(null);
        $notificationManager->notify([$author], Argument::any(), Argument::any(), Argument::any())->willReturn(null);
        $this->notifyUsers($jobExecutionEvent);
    }
}
