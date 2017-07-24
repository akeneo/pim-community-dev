<?php

namespace spec\PimEnterprise\Component\Api\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Api\Normalizer\ProductNormalizer;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $productNormalizer,
        ProductDraftRepositoryInterface $productDraftRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith($productNormalizer, $productDraftRepository, $authorizationChecker, $tokenStorage);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductNormalizer::class);
    }

    function it_supports_product_normalization($productNormalizer, ProductInterface $product)
    {
        $productNormalizer
            ->supportsNormalization($product, 'external_api')
            ->shouldBeCalled()
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
        $token->getUsername()->willReturn('Kevin');

        $productDraftRepository->findUserProductDraft($product, 'Kevin')->willReturn(null);

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
        ProductDraftInterface $productDraft,
        TokenInterface $token,
        $productNormalizer,
        $authorizationChecker,
        $productDraftRepository,
        $tokenStorage
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUsername()->willReturn('Kevin');

        $productDraftRepository->findUserProductDraft($product, 'Kevin')->willReturn($productDraft);
        $productDraft->getStatus()->willReturn(ProductDraftInterface::IN_PROGRESS);

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
        ProductDraftInterface $productDraft,
        TokenInterface $token,
        $productNormalizer,
        $authorizationChecker,
        $productDraftRepository,
        $tokenStorage
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUsername()->willReturn('Kevin');

        $productDraftRepository->findUserProductDraft($product, 'Kevin')->willReturn($productDraft);
        $productDraft->getStatus()->willReturn(ProductDraftInterface::READY);

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
