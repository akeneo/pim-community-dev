<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Tasklet;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager;
use Akeneo\Pim\Permission\Component\Attributes as SecurityAttributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ApproveTaskletSpec extends ObjectBehavior
{
    function let(
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        EntityWithValuesDraftManager $productDraftManager,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        EntityWithValuesDraftManager $productModelDraftManager,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductDraftChangesPermissionHelper $permissionHelper,
        StepExecution $stepExecution,
        JobStopper $jobStopper
    ) {
        $this->beConstructedWith(
            $productDraftRepository,
            $productDraftManager,
            $productModelDraftRepository,
            $productModelDraftManager,
            $authorizationChecker,
            $permissionHelper,
            $jobStopper
        );
        $this->setStepExecution($stepExecution);
    }

    function it_approves_valid_proposals(
        $productDraftRepository,
        $productDraftManager,
        $productModelDraftRepository,
        $productModelDraftManager,
        $authorizationChecker,
        $permissionHelper,
        $stepExecution,
        ProductDraft $productDraft1,
        ProductDraft $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters,
        ProductModelDraft $productModelDraft,
        ProductModel $productModel,
        JobStopper $jobStopper
    ) {
        $configuration = ['productDraftIds' => [1, 2], 'productModelDraftIds' => [1], 'comment' => null];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('productDraftIds')->willReturn($configuration['productDraftIds']);
        $jobParameters->get('productModelDraftIds')->willReturn($configuration['productModelDraftIds']);
        $jobParameters->get('comment')->willReturn($configuration['comment']);

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

        $stepExecution->incrementSummaryInfo('approved')->shouldBeCalledTimes(3);
        $this->setStepExecution($stepExecution);

        $productDraftManager->approve($productDraft1, ['comment' => null])->shouldBeCalled();
        $productDraftManager->approve($productDraft2, ['comment' => null])->shouldBeCalled();
        $productModelDraftManager->approve($productModelDraft, ['comment' => null])->shouldBeCalled();
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_skips_proposals_if_not_ready(
        $productDraftRepository,
        $productDraftManager,
        $productModelDraftRepository,
        $productModelDraftManager,
        $authorizationChecker,
        $permissionHelper,
        $stepExecution,
        ProductDraft $productDraft1,
        ProductDraft $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters,
        ProductModelDraft $productModelDraft,
        ProductModel $productModel,
        JobStopper $jobStopper
    ) {
        $configuration = ['productDraftIds' => [1, 2], 'productModelDraftIds' => [1], 'comment' => null];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('productDraftIds')->willReturn($configuration['productDraftIds']);
        $jobParameters->get('productModelDraftIds')->willReturn($configuration['productModelDraftIds']);
        $jobParameters->get('comment')->willReturn($configuration['comment']);

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);
        $productModelDraftRepository->findByIds(Argument::any())->willReturn(null);

        $productDraft1->getStatus()->willReturn(EntityWithValuesDraftInterface::IN_PROGRESS);
        $productDraft1->getEntityWithValue()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft1)->willReturn(true);

        $productDraft2->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productDraft2->getEntityWithValue()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft2)->willReturn(true);

        $productModelDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::IN_PROGRESS);
        $productModelDraft->getEntityWithValue()->willReturn($productModel);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $productModel)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productModelDraft)->willReturn(true);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('approved')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $productDraftManager->approve($productDraft1, ['comment' => null])->shouldNotBeCalled();
        $productDraftManager->approve($productDraft2, ['comment' => null])->shouldBeCalled();
        $productModelDraftManager->approve($productModelDraft, ['comment' => null])->shouldNotBeCalled();
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_skips_proposals_if_user_does_not_own_the_product(
        $productDraftRepository,
        $productDraftManager,
        $productModelDraftRepository,
        $productModelDraftManager,
        $authorizationChecker,
        $permissionHelper,
        $stepExecution,
        ProductDraft $productDraft1,
        ProductDraft $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters,
        ProductModelDraft $productModelDraft,
        ProductModel $productModel,
        JobStopper $jobStopper
    ) {
        $configuration = ['productDraftIds' => [1, 2], 'productModelDraftIds' => [1], 'comment' => null];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('productDraftIds')->willReturn($configuration['productDraftIds']);
        $jobParameters->get('productModelDraftIds')->willReturn($configuration['productModelDraftIds']);
        $jobParameters->get('comment')->willReturn($configuration['comment']);

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
        $stepExecution->incrementSummaryInfo('approved')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $productDraftManager->approve($productDraft1, ['comment' => null])->shouldNotBeCalled();
        $productDraftManager->approve($productDraft2, ['comment' => null])->shouldBeCalled();
        $productModelDraftManager->approve($productModelDraft, ['comment' => null])->shouldNotBeCalled();
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_skips_with_warning_proposals_if_user_cannot_edit_the_attributes(
        $productDraftRepository,
        $productDraftManager,
        $authorizationChecker,
        $permissionHelper,
        $stepExecution,
        ProductDraft $productDraft1,
        ProductDraft $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters,
        JobStopper $jobStopper
    ) {
        $configuration = ['productDraftIds' => [1, 2], 'productModelDraftIds' => [], 'comment' => null];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('productDraftIds')->willReturn($configuration['productDraftIds']);
        $jobParameters->get('productModelDraftIds')->willReturn($configuration['productModelDraftIds']);
        $jobParameters->get('comment')->willReturn($configuration['comment']);

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);

        $productDraft1->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productDraft1->getEntityWithValue()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft1)->willReturn(false);

        $productDraft2->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productDraft2->getEntityWithValue()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($productDraft2)->willReturn(true);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('approved')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $productDraftManager->approve($productDraft1, ['comment' => null])->shouldNotBeCalled();
        $productDraftManager->approve($productDraft2, ['comment' => null])->shouldBeCalled();
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }
}
