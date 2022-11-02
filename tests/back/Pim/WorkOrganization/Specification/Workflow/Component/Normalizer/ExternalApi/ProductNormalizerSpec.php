<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\ExternalApi;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\ExternalApi\ProductNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $productNormalizer,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith($productNormalizer, $productDraftRepository, $authorizationChecker, $tokenStorage);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductNormalizer::class);
    }

    function it_should_implement()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_product_normalization($productNormalizer, ProductInterface $product)
    {
        $productNormalizer
            ->supportsNormalization($product, 'external_api')
            ->willReturn(true);

        $this->supportsNormalization($product, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_product_for_an_owner(ProductInterface $product, $productNormalizer, $authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);
        $productNormalizer->normalize($product, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $this->normalize($product, 'external_api')->shouldReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
            'metadata'   => [
                'workflow_status' => 'working_copy',
            ]
        ]);
    }

    function it_normalizes_a_product_for_an_user_who_has_only_view_permission(ProductInterface $product, $productNormalizer, $authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW, $product)->willReturn(true);

        $productNormalizer->normalize($product, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $this->normalize($product, 'external_api')->shouldReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
            'metadata'   => [
                'workflow_status' => 'read_only',
            ]
        ]);
    }

    function it_normalizes_a_product_without_draft_for_an_user_who_can_edit(
        ProductInterface $product,
        TokenInterface $token,
        $productNormalizer,
        $authorizationChecker,
        $productDraftRepository,
        $tokenStorage
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUserIdentifier()->willReturn('Kevin');

        $productDraftRepository->findUserEntityWithValuesDraft($product, 'Kevin')->willReturn(null);

        $productNormalizer->normalize($product, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $this->normalize($product, 'external_api')->shouldReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
            'metadata'   => [
                'workflow_status' => 'working_copy',
            ]
        ]);
    }

    function it_normalizes_a_product_with_a_draft_in_progress(
        ProductInterface $product,
        EntityWithValuesDraftInterface $productDraft,
        TokenInterface $token,
        $productNormalizer,
        $authorizationChecker,
        $productDraftRepository,
        $tokenStorage
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUserIdentifier()->willReturn('Kevin');

        $productDraftRepository->findUserEntityWithValuesDraft($product, 'Kevin')->willReturn($productDraft);
        $productDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::IN_PROGRESS);

        $productNormalizer->normalize($product, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $this->normalize($product, 'external_api')->shouldReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
            'metadata'   => [
                'workflow_status' => 'draft_in_progress',
            ]
        ]);
    }

    function it_normalizes_a_product_with_a_draft_waiting_for_approval(
        ProductInterface $product,
        EntityWithValuesDraftInterface $productDraft,
        TokenInterface $token,
        $productNormalizer,
        $authorizationChecker,
        $productDraftRepository,
        $tokenStorage
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUserIdentifier()->willReturn('Kevin');

        $productDraftRepository->findUserEntityWithValuesDraft($product, 'Kevin')->willReturn($productDraft);
        $productDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);

        $productNormalizer->normalize($product, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $this->normalize($product, 'external_api')->shouldReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
            'metadata'   => [
                'workflow_status' => 'proposal_waiting_for_approval',
            ]
        ]);
    }

    function it_throws_an_exception_if_the_user_has_not_view_permission(ProductInterface $product, $productNormalizer, $authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW, $product)->willReturn(false);

        $productNormalizer->normalize($product, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $this->shouldThrow(
            new \LogicException('A product should not be normalized if the user has not the "view" permission on it.')
        )->during('normalize', [$product, 'external_api']);
    }
}
