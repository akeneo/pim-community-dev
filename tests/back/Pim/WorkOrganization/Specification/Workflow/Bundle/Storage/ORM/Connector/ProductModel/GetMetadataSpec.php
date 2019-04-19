<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\ORM\Connector\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\ORM\Connector\ProductModel\GetMetadata;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GetMetadataSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        TokenInterface $token
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUsername()->willReturn('mary');

        $this->beConstructedWith($authorizationChecker, $tokenStorage, $draftRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetMetadata::class);
    }

    function it_gets_workflow_status_for_a_product_for_a_user_who_has_only_view_permission(
        ProductModelInterface $productModel,
        $authorizationChecker
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW, $productModel)->willReturn(true);

        $this->forProductModel($productModel)->shouldReturn(['workflow_status' => 'read_only']);
    }

    function it_gets_workflow_status_for_a_product_for_a_user_who_has_own_permission(
        ProductModelInterface $productModel,
        $authorizationChecker
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(true);

        $this->forProductModel($productModel)->shouldReturn(['workflow_status' => 'working_copy']);
    }

    function it_gets_workflow_status_for_a_product_without_draft_for_a_user_with_edit_permission(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        ProductModelInterface $productModel
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $productModel)->willReturn(true);

        $draftRepository->findUserEntityWithValuesDraft($productModel, 'mary')->willReturn(null);

        $this->forProductModel($productModel)->shouldReturn(['workflow_status' => 'working_copy']);
    }

    function it_gets_workflow_status_for_a_product_with_in_progress_draft_for_a_user_with_edit_permission(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        EntityWithValuesDraftInterface $draft,
        ProductModelInterface $productModel
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $productModel)->willReturn(true);

        $draftRepository->findUserEntityWithValuesDraft($productModel, 'mary')->willReturn($draft);
        $draft->getStatus()->willReturn(EntityWithValuesDraftInterface::IN_PROGRESS);

        $this->forProductModel($productModel)->shouldReturn(['workflow_status' => 'draft_in_progress']);
    }

    function it_gets_workflow_status_for_a_product_with_ready_draft_for_a_user_with_edit_permission(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        EntityWithValuesDraftInterface $draft,
        ProductModelInterface $productModel
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $productModel)->willReturn(true);

        $draftRepository->findUserEntityWithValuesDraft($productModel, 'mary')->willReturn($draft);
        $draft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);

        $this->forProductModel($productModel)->shouldReturn(['workflow_status' => 'proposal_waiting_for_approval']);
    }

    function it_throws_an_exception_if_the_user_has_no_view_permission(
        AuthorizationCheckerInterface $authorizationChecker,
        ProductModelInterface $productModel
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW, $productModel)->willReturn(false);

        $this->shouldThrow(\LogicException::class)->during('forProductModel', [$productModel]);
    }
}
