<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Connector\Tasklet;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ApproveTaskletSpec extends ObjectBehavior
{
    function let(
        ProductDraftRepositoryInterface $productDraftRepository,
        ProductDraftManager $productDraftManager,
        UserProviderInterface $userProvider,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith(
            $productDraftRepository,
            $productDraftManager,
            $userProvider,
            $authorizationChecker,
            $tokenStorage
        );
    }

    function it_approves_valid_proposals(
        $productDraftRepository,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
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
        $authorizationChecker->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft1)->willReturn(true);

        $productDraft2->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft2->getProduct()->willReturn($product2);
        $authorizationChecker->isGranted(Attributes::OWN, $product2)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft2)->willReturn(true);

        $stepExecution->incrementSummaryInfo('approved')->shouldBeCalledTimes(2);
        $this->setStepExecution($stepExecution);

        $this->execute(['draftIds' => [1, 2]]);
    }

    function it_skips_proposals_if_not_ready(
        $productDraftRepository,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
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
        $authorizationChecker->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft1)->willReturn(true);

        $productDraft2->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft2->getProduct()->willReturn($product2);
        $authorizationChecker->isGranted(Attributes::OWN, $product2)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft2)->willReturn(true);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('approved')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $this->execute(['draftIds' => [1, 2]]);
    }

    function it_skips_proposals_if_user_does_not_own_the_product(
        $productDraftRepository,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
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
        $authorizationChecker->isGranted(Attributes::OWN, $product1)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft1)->willReturn(true);

        $productDraft2->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft2->getProduct()->willReturn($product2);
        $authorizationChecker->isGranted(Attributes::OWN, $product2)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft2)->willReturn(true);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('approved')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $this->execute(['draftIds' => [1, 2]]);
    }

    function it_skips_proposals_if_user_cannot_edit_the_attributes(
        $productDraftRepository,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
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
        $authorizationChecker->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft1)->willReturn(false);

        $productDraft2->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft2->getProduct()->willReturn($product2);
        $authorizationChecker->isGranted(Attributes::OWN, $product2)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft2)->willReturn(true);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('approved')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $this->execute(['draftIds' => [1, 2]]);
    }

    function it_skips_invalid_proposals(
        $productDraftRepository,
        $productDraftManager,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
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
        $authorizationChecker->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft1)->willReturn(true);

        $productDraft2->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft2->getProduct()->willReturn($product2);
        $authorizationChecker->isGranted(Attributes::OWN, $product2)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft2)->willReturn(true);

        $productDraftManager->approve($productDraft1)->willThrow(new ValidatorException());
        $productDraftManager->approve($productDraft2)->shouldBeCalled();

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('approved')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $this->execute(['draftIds' => [1, 2]]);
    }
}
