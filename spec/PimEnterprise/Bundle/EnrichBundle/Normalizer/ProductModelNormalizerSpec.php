<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Workflow\Applier\DraftApplierInterface;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        DraftApplierInterface $draftApplier,
        CategoryAccessRepository $categoryAccessRepo,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $productModelRepository
    ) {
        $this->beConstructedWith(
            $normalizer,
            $draftRepository,
            $draftApplier,
            $categoryAccessRepo,
            $tokenStorage,
            $authorizationChecker,
            $productModelRepository
            );
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_normalizes(
        $productModelRepository,
        $normalizer,
        $authorizationChecker,
        $tokenStorage,
        $draftRepository,
        $draftApplier,
        ProductModelInterface $productModel,
        ProductModelInterface $workingCopy,
        EntityWithValuesDraftInterface $productModelDraft,
        TokenInterface $token
    ) {
        $productModel->getCode()->willReturn('code');
        $productModelRepository->findOneByIdentifier('code')->willReturn($workingCopy);
        $normalizer->normalize($workingCopy, 'standard', [])->willReturn(['normalizedWorkedCopy']);

        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $productModel)->willReturn(true);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUsername()->willReturn('username');

        $productModelDraft->getStatus()->willReturn(1);

        $draftRepository->findUserEntityWithValuesDraft($productModel, 'username')->willReturn($productModelDraft);
        $draftApplier->applyAllChanges($productModel, $productModelDraft)->shouldBeCalled();

        $normalizedProductModel = [
            "meta" => [
                "is_owner"     => false,
                "working_copy" => ["normalizedWorkedCopy"],

                "draft_status" => 1,
            ],
        ];

        $normalizer->normalize($productModel, 'internal_api', [])->willReturn($normalizedProductModel);

        $this->normalize($productModel)->shouldReturn($normalizedProductModel);
    }
}
