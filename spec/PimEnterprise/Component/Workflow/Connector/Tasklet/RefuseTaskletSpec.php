<?php

namespace spec\PimEnterprise\Component\Workflow\Connector\Tasklet;

use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Helper\ProductDraftChangesPermissionHelper;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Component\Security\Attributes as SecurityAttributes;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class RefuseTaskletSpec extends ObjectBehavior
{
    function let(
        ProductDraftRepositoryInterface $productDraftRepository,
        ProductDraftManager $productDraftManager,
        UserProviderInterface $userProvider,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        ProductDraftChangesPermissionHelper $permissionHelper,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $productDraftRepository,
            $productDraftManager,
            $userProvider,
            $authorizationChecker,
            $tokenStorage,
            $permissionHelper
        );
        $this->setStepExecution($stepExecution);
    }

    function it_refuses_proposals(
        $productDraftRepository,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        $permissionHelper,
        $stepExecution,
        UserInterface $userJulia,
        JobExecution $jobExecution,
        ProductDraftInterface $productDraft1,
        ProductDraftInterface $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters
    ) {
        $configuration = ['draftIds' => [1, 2], 'comment' => null];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('draftIds')->willReturn($configuration['draftIds']);
        $jobParameters->get('comment')->willReturn($configuration['comment']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $userProvider->loadUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);

        $productDraft1->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft1->getProduct()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft1)->willReturn(true);

        $productDraft2->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft2->getProduct()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft2)->willReturn(true);

        $stepExecution->incrementSummaryInfo('refused')->shouldBeCalledTimes(2);
        $this->setStepExecution($stepExecution);

        $this->execute();
    }

    function it_skips_proposals_if_user_does_not_own_the_product(
        $productDraftRepository,
        $productDraftManager,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        $permissionHelper,
        $stepExecution,
        UserInterface $userJulia,
        JobExecution $jobExecution,
        ProductDraftInterface $productDraft1,
        ProductDraftInterface $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters
    ) {
        $configuration = ['draftIds' => [1, 2], 'comment' => null];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('draftIds')->willReturn($configuration['draftIds']);
        $jobParameters->get('comment')->willReturn($configuration['comment']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $userProvider->loadUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);

        $productDraft1->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft1->getProduct()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(false);
        $permissionHelper->canEditOneChangeToReview($productDraft1)->willReturn(true);

        $productDraft2->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft2->getProduct()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft2)->willReturn(true);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('refused')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $productDraftManager->refuse($productDraft1, ['comment' => null])->shouldNotBeCalled();
        $productDraftManager->refuse($productDraft2, ['comment' => null])->shouldBeCalled();

        $this->execute();
    }

    function it_skips_with_warning_proposals_if_no_change_can_be_refused(
        $productDraftRepository,
        $productDraftManager,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        $permissionHelper,
        $stepExecution,
        UserInterface $userJulia,
        JobExecution $jobExecution,
        ProductDraftInterface $productDraft1,
        ProductDraftInterface $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters
    ) {
        $configuration = ['draftIds' => [1, 2], 'comment' => null];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('draftIds')->willReturn($configuration['draftIds']);
        $jobParameters->get('comment')->willReturn($configuration['comment']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $userProvider->loadUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);

        $productDraft1->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft1->getProduct()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft1)->willReturn(false);

        $productDraft2->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft2->getProduct()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft2)->willReturn(true);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('refused')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $productDraftManager->refuse($productDraft1, ['comment' => null])->shouldNotBeCalled();
        $productDraftManager->refuse($productDraft2, ['comment' => null])->shouldBeCalled();

        $this->execute();
    }

    function it_refuses_proposals_with_a_comment(
        $productDraftRepository,
        $productDraftManager,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        $permissionHelper,
        $stepExecution,
        UserInterface $userJulia,
        JobExecution $jobExecution,
        ProductDraftInterface $productDraft1,
        ProductDraftInterface $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters
    ) {
        $configuration = ['draftIds' => [1, 2], 'comment' => 'Please fix the typo.'];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('draftIds')->willReturn($configuration['draftIds']);
        $jobParameters->get('comment')->willReturn($configuration['comment']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $userProvider->loadUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);

        $productDraft1->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft1->getProduct()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft1)->willReturn(true);

        $productDraft2->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft2->getProduct()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft2)->willReturn(true);

        $stepExecution->incrementSummaryInfo('refused')->shouldBeCalledTimes(2);
        $this->setStepExecution($stepExecution);

        $productDraftManager->refuse($productDraft1, ['comment' => 'Please fix the typo.'])->shouldBeCalled();
        $productDraftManager->refuse($productDraft2, ['comment' => 'Please fix the typo.'])->shouldBeCalled();

        $this->execute();
    }
}
