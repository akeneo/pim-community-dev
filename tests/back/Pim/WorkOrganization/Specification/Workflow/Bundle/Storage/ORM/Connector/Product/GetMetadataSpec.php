<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\ORM\Connector\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\ORM\Connector\Product\GetMetadata;
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

    function it_gets_workflow_status_for_a_product_for_a_user_who_has_only_view_permission(ProductInterface $product, $authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW, $product)->willReturn(true);

        $this->forProduct($product)->shouldReturn(['workflow_status' => 'read_only']);
    }

    function it_gets_workflow_status_for_a_product_for_a_user_who_has_own_permission(ProductInterface $product, $authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);

        $this->forProduct($product)->shouldReturn(['workflow_status' => 'working_copy']);
    }

    function it_gets_workflow_status_for_a_product_without_draft_for_a_user_with_edit_permission(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        ProductInterface $product
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);

        $draftRepository->findUserEntityWithValuesDraft($product, 'mary')->willReturn(null);

        $this->forProduct($product)->shouldReturn(['workflow_status' => 'working_copy']);
    }

    function it_gets_workflow_status_for_a_product_with_in_progress_draft_for_a_user_with_edit_permission(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        EntityWithValuesDraftInterface $draft,
        ProductInterface $product
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);

        $draftRepository->findUserEntityWithValuesDraft($product, 'mary')->willReturn($draft);
        $draft->getStatus()->willReturn(EntityWithValuesDraftInterface::IN_PROGRESS);

        $this->forProduct($product)->shouldReturn(['workflow_status' => 'draft_in_progress']);
    }

    function it_gets_workflow_status_for_a_product_with_ready_draft_for_a_user_with_edit_permission(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        EntityWithValuesDraftInterface $draft,
        ProductInterface $product
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);

        $draftRepository->findUserEntityWithValuesDraft($product, 'mary')->willReturn($draft);
        $draft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);

        $this->forProduct($product)->shouldReturn(['workflow_status' => 'proposal_waiting_for_approval']);
    }

    function it_throws_an_exception_if_the_user_has_no_view_permission(
        AuthorizationCheckerInterface $authorizationChecker,
        ProductInterface $product
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW, $product)->willReturn(false);

        $this->shouldThrow(\LogicException::class)->during('forProduct', [$product]);
    }
}
