<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\ExternalApi\ProductModelNormalizer;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $productModelNormalizer,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith($productModelNormalizer, $productModelDraftRepository, $authorizationChecker, $tokenStorage);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(
            ProductModelNormalizer::class);
    }

    function it_should_implement()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_product_model_normalization($productModelNormalizer, ProductModelInterface $productModel)
    {
        $productModelNormalizer->supportsNormalization($productModel, 'external_api')->willReturn(true);

        $this->supportsNormalization($productModel, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_product_model_for_an_owner(ProductModelInterface $productModel, $productModelNormalizer, $authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(true);
        $productModelNormalizer->normalize($productModel, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $this->normalize($productModel, 'external_api')->shouldReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
            'metadata'   => [
                'workflow_status' => 'working_copy',
            ]
        ]);
    }

    function it_normalizes_a_product_model_for_an_user_who_has_only_view_permission(
        ProductModelInterface $productModel,
        $productModelNormalizer,
        $authorizationChecker
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW, $productModel)->willReturn(true);

        $productModelNormalizer->normalize($productModel, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $this->normalize($productModel, 'external_api')->shouldReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
            'metadata'   => [
                'workflow_status' => 'read_only',
            ]
        ]);
    }

    function it_normalizes_a_product_model_without_draft_for_an_user_who_can_edit(
        ProductModelInterface $productproductModel,
        TokenInterface $token,
        $productModelNormalizer,
        $authorizationChecker,
        $productModelDraftRepository,
        $tokenStorage
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $productproductModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $productproductModel)->willReturn(true);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUserIdentifier()->willReturn('Kevin');

        $productModelDraftRepository->findUserEntityWithValuesDraft($productproductModel, 'Kevin')->willReturn(null);

        $productModelNormalizer->normalize($productproductModel, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $this->normalize($productproductModel, 'external_api')->shouldReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
            'metadata'   => [
                'workflow_status' => 'working_copy',
            ]
        ]);
    }

    function it_normalizes_a_product_model_with_a_draft_in_progress(
        ProductModelInterface $productModel,
        EntityWithValuesDraftInterface $productModelDraft,
        TokenInterface $token,
        $productModelNormalizer,
        $authorizationChecker,
        $productModelDraftRepository,
        $tokenStorage
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $productModel)->willReturn(true);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUserIdentifier()->willReturn('Kevin');

        $productModelDraftRepository->findUserEntityWithValuesDraft($productModel, 'Kevin')->willReturn($productModelDraft);
        $productModelDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::IN_PROGRESS);

        $productModelNormalizer->normalize($productModel, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $this->normalize($productModel, 'external_api')->shouldReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
            'metadata'   => [
                'workflow_status' => 'draft_in_progress',
            ]
        ]);
    }

    function it_normalizes_a_product_model_with_a_draft_waiting_for_approval(
        ProductModelInterface $productModel,
        EntityWithValuesDraftInterface $productModelDraft,
        TokenInterface $token,
        $productModelNormalizer,
        $authorizationChecker,
        $productModelDraftRepository,
        $tokenStorage
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $productModel)->willReturn(true);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUserIdentifier()->willReturn('Kevin');

        $productModelDraftRepository->findUserEntityWithValuesDraft($productModel, 'Kevin')->willReturn($productModelDraft);
        $productModelDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);

        $productModelNormalizer->normalize($productModel, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $this->normalize($productModel, 'external_api')->shouldReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
            'metadata'   => [
                'workflow_status' => 'proposal_waiting_for_approval',
            ]
        ]);
    }

    function it_throws_an_exception_if_the_user_has_not_view_permission(
        ProductModelInterface $productModel,
        $productModelNormalizer,
        $authorizationChecker
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW, $productModel)->willReturn(false);

        $productModelNormalizer->normalize($productModel, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $this->shouldThrow(
            new \LogicException('A product model should not be normalized if the user has not the "view" permission on it.')
        )->during('normalize', [$productModel, 'external_api']);
    }
}
