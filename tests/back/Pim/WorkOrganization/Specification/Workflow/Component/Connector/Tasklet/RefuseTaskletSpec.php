<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Tasklet;

use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager;
use Akeneo\Pim\Permission\Component\Attributes as SecurityAttributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class RefuseTaskletSpec extends ObjectBehavior
{
    function let(
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        EntityWithValuesDraftManager $productDraftManager,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        EntityWithValuesDraftManager $productModelDraftManager,
        UserProviderInterface $userProvider,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        ProductDraftChangesPermissionHelper $permissionHelper,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $productDraftRepository,
            $productDraftManager,
            $productModelDraftRepository,
            $productModelDraftManager,
            $userProvider,
            $authorizationChecker,
            $tokenStorage,
            $permissionHelper
        );
        $this->setStepExecution($stepExecution);
    }

    function it_refuses_proposals(
        $productDraftRepository,
        $productDraftManager,
        $productModelDraftRepository,
        $productModelDraftManager,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        $permissionHelper,
        $stepExecution,
        UserInterface $userJulia,
        JobExecution $jobExecution,
        ProductDraft $productDraft1,
        ProductDraft $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters,
        ProductModelDraft $productModelDraft,
        ProductModel $productModel
    ) {
        $configuration = ['productDraftIds' => [1, 2], 'productModelDraftIds' => [1], 'comment' => null];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('productDraftIds')->willReturn($configuration['productDraftIds']);
        $jobParameters->get('productModelDraftIds')->willReturn($configuration['productModelDraftIds']);
        $jobParameters->get('comment')->willReturn($configuration['comment']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $userProvider->loadUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);
        $productModelDraftRepository->findByIds(Argument::any())->willReturn([$productModelDraft]);

        $productDraft1->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productDraft1->getEntityWithValue()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft1)->willReturn(true);

        $productDraft2->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productDraft2->getEntityWithValue()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft2)->willReturn(true);

        $productModelDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productModelDraft->getEntityWithValue()->willReturn($productModel);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $productModel)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productModelDraft)->willReturn(true);

        $stepExecution->incrementSummaryInfo('refused')->shouldBeCalledTimes(3);
        $this->setStepExecution($stepExecution);

        $productDraftManager->refuse($productDraft1, ['comment' => null])->shouldBeCalled();
        $productDraftManager->refuse($productDraft2, ['comment' => null])->shouldBeCalled();
        $productModelDraftManager->refuse($productModelDraft, ['comment' => null])->shouldBeCalled();

        $this->execute();
    }

    function it_skips_proposals_if_user_does_not_own_the_product(
        $productDraftRepository,
        $productDraftManager,
        $productModelDraftRepository,
        $productModelDraftManager,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        $permissionHelper,
        $stepExecution,
        UserInterface $userJulia,
        JobExecution $jobExecution,
        ProductDraft $productDraft1,
        ProductDraft $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters,
        ProductModelDraft $productModelDraft,
        ProductModel $productModel
    ) {
        $configuration = ['productDraftIds' => [1, 2], 'productModelDraftIds' => [1], 'comment' => null];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('productDraftIds')->willReturn($configuration['productDraftIds']);
        $jobParameters->get('productModelDraftIds')->willReturn($configuration['productModelDraftIds']);
        $jobParameters->get('comment')->willReturn($configuration['comment']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $userProvider->loadUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);
        $productModelDraftRepository->findByIds(Argument::any())->willReturn([$productModelDraft]);

        $productDraft1->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productDraft1->getEntityWithValue()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(false);
        $permissionHelper->canEditOneChangeToReview($productDraft1)->willReturn(true);

        $productDraft2->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productDraft2->getEntityWithValue()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft2)->willReturn(true);

        $productModelDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productModelDraft->getEntityWithValue()->willReturn($productModel);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $productModel)->willReturn(false);
        $permissionHelper->canEditOneChangeToReview($productModelDraft)->willReturn(true);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(2);
        $stepExecution->incrementSummaryInfo('refused')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $productDraftManager->refuse($productDraft1, ['comment' => null])->shouldNotBeCalled();
        $productDraftManager->refuse($productDraft2, ['comment' => null])->shouldBeCalled();
        $productModelDraftManager->refuse($productModelDraft, ['comment' => null])->shouldNotBeCalled();

        $this->execute();
    }

    function it_skips_with_warning_proposals_if_no_change_can_be_refused(
        $productDraftRepository,
        $productDraftManager,
        $productModelDraftRepository,
        $productModelDraftManager,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        $permissionHelper,
        $stepExecution,
        UserInterface $userJulia,
        JobExecution $jobExecution,
        ProductDraft $productDraft1,
        ProductDraft $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters,
        ProductModelDraft $productModelDraft,
        ProductModel $productModel
    ) {
        $configuration = ['productDraftIds' => [1, 2], 'productModelDraftIds' => [1], 'comment' => null];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('productDraftIds')->willReturn($configuration['productDraftIds']);
        $jobParameters->get('productModelDraftIds')->willReturn($configuration['productModelDraftIds']);
        $jobParameters->get('comment')->willReturn($configuration['comment']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $userProvider->loadUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);
        $productModelDraftRepository->findByIds(Argument::any())->willReturn([$productModelDraft]);

        $productDraft1->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productDraft1->getEntityWithValue()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft1)->willReturn(false);

        $productDraft2->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productDraft2->getEntityWithValue()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft2)->willReturn(true);

        $productModelDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productModelDraft->getEntityWithValue()->willReturn($productModel);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $productModel)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productModelDraft)->willReturn(false);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(2);
        $stepExecution->incrementSummaryInfo('refused')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $productDraftManager->refuse($productDraft1, ['comment' => null])->shouldNotBeCalled();
        $productDraftManager->refuse($productDraft2, ['comment' => null])->shouldBeCalled();
        $productModelDraftManager->refuse($productModelDraft, ['comment' => null])->shouldNotBeCalled();

        $this->execute();
    }

    function it_refuses_proposals_with_a_comment(
        $productDraftRepository,
        $productDraftManager,
        $productModelDraftRepository,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        $permissionHelper,
        $stepExecution,
        UserInterface $userJulia,
        JobExecution $jobExecution,
        ProductDraft $productDraft1,
        ProductDraft $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters
    ) {
        $configuration = ['productDraftIds' => [1, 2], 'productModelDraftIds' => [], 'comment' => 'Please fix the typo.'];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('productDraftIds')->willReturn($configuration['productDraftIds']);
        $jobParameters->get('productModelDraftIds')->willReturn($configuration['productModelDraftIds']);
        $jobParameters->get('comment')->willReturn($configuration['comment']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $userProvider->loadUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);
        $productModelDraftRepository->findByIds(Argument::any())->shouldBeCalled();

        $productDraft1->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productDraft1->getEntityWithValue()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft1)->willReturn(true);

        $productDraft2->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productDraft2->getEntityWithValue()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft2)->willReturn(true);

        $stepExecution->incrementSummaryInfo('refused')->shouldBeCalledTimes(2);
        $this->setStepExecution($stepExecution);

        $productDraftManager->refuse($productDraft1, ['comment' => 'Please fix the typo.'])->shouldBeCalled();
        $productDraftManager->refuse($productDraft2, ['comment' => 'Please fix the typo.'])->shouldBeCalled();

        $this->execute();
    }
}
