<?php

namespace spec\PimEnterprise\Component\Workflow\Connector\Tasklet;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModel;
use PimEnterprise\Bundle\WorkflowBundle\Helper\ProductDraftChangesPermissionHelper;
use PimEnterprise\Bundle\WorkflowBundle\Manager\EntityWithValuesDraftManager;
use PimEnterprise\Component\Security\Attributes as SecurityAttributes;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraft;
use PimEnterprise\Component\Workflow\Model\ProductModelDraft;
use PimEnterprise\Component\Workflow\Repository\EntityWithValuesDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

class ApproveTaskletSpec extends ObjectBehavior
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
        ProductModel $productModel
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
        ProductModel $productModel
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
        ProductModel $productModel
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
        JobParameters $jobParameters
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

        $this->execute();
    }
}
