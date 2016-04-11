<?php

namespace spec\PimEnterprise\Component\Workflow\Connector\Tasklet;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes as SecurityAttributes;
use PimEnterprise\Bundle\WorkflowBundle\Helper\ProductDraftChangesPermissionHelper;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

class ApproveTaskletSpec extends ObjectBehavior
{
    function let(
        ProductDraftRepositoryInterface $productDraftRepository,
        ProductDraftManager $productDraftManager,
        UserProviderInterface $userProvider,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        ProductDraftChangesPermissionHelper $permissionHelper
    ) {
        $this->beConstructedWith(
            $productDraftRepository,
            $productDraftManager,
            $userProvider,
            $authorizationChecker,
            $tokenStorage,
            $permissionHelper
        );
    }

    function it_approves_valid_proposals(
        $productDraftRepository,
        $productDraftManager,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        $permissionHelper,
        UserInterface $userJulia,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        ProductDraftInterface $productDraft1,
        ProductDraftInterface $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
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

        $stepExecution->incrementSummaryInfo('approved')->shouldBeCalledTimes(2);
        $this->setStepExecution($stepExecution);

        $productDraftManager->approve($productDraft1, ['comment' => null])->shouldBeCalled();
        $productDraftManager->approve($productDraft2, ['comment' => null])->shouldBeCalled();

        $this->execute(['draftIds' => [1, 2], 'comment' => null]);
    }

    function it_skips_proposals_if_not_ready(
        $productDraftRepository,
        $productDraftManager,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        $permissionHelper,
        UserInterface $userJulia,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        ProductDraftInterface $productDraft1,
        ProductDraftInterface $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $userProvider->loadUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);

        $productDraft1->getStatus()->willReturn(ProductDraftInterface::IN_PROGRESS);
        $productDraft1->getProduct()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft1)->willReturn(true);

        $productDraft2->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft2->getProduct()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft2)->willReturn(true);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('approved')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $productDraftManager->approve($productDraft1, ['comment' => null])->shouldNotBeCalled();
        $productDraftManager->approve($productDraft2, ['comment' => null])->shouldBeCalled();

        $this->execute(['draftIds' => [1, 2], 'comment' => null]);
    }

    function it_skips_proposals_if_user_does_not_own_the_product(
        $productDraftRepository,
        $productDraftManager,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        $permissionHelper,
        UserInterface $userJulia,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        ProductDraftInterface $productDraft1,
        ProductDraftInterface $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
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
        $stepExecution->incrementSummaryInfo('approved')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $productDraftManager->approve($productDraft1, ['comment' => null])->shouldNotBeCalled();
        $productDraftManager->approve($productDraft2, ['comment' => null])->shouldBeCalled();

        $this->execute(['draftIds' => [1, 2], 'comment' => null]);
    }

    function it_skips_with_warning_proposals_if_user_cannot_edit_the_attributes(
        $productDraftRepository,
        $productDraftManager,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        $permissionHelper,
        UserInterface $userJulia,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        ProductDraftInterface $productDraft1,
        ProductDraftInterface $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
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
        $stepExecution->incrementSummaryInfo('approved')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $productDraftManager->approve($productDraft1, ['comment' => null])->shouldNotBeCalled();
        $productDraftManager->approve($productDraft2, ['comment' => null])->shouldBeCalled();

        $this->execute(['draftIds' => [1, 2], 'comment' => null]);
    }
}
