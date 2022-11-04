<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MissingRequiredAttributesCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\MissingRequiredAttributesNormalizerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer\ProductNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplierInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        PublishedProductManager $publishedManager,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        DraftApplierInterface $draftApplier,
        CategoryAccessRepository $categoryAccessRepo,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductRepositoryInterface $productRepository,
        MissingRequiredAttributesCalculatorInterface $missingRequiredAttributesCalculator,
        MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer,
        NormalizerInterface $chainedNormalizer,
        TokenInterface $token
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $this->beConstructedWith(
            $normalizer,
            $publishedManager,
            $draftRepository,
            $draftApplier,
            $categoryAccessRepo,
            $tokenStorage,
            $authorizationChecker,
            $productRepository,
            $missingRequiredAttributesCalculator,
            $missingRequiredAttributesNormalizer
        );
        $this->setNormalizer($chainedNormalizer);
    }

    function it_is_a_product_normalizer()
    {
        $this->shouldHaveType(ProductNormalizer::class);
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_can_only_normalize_products_for_internal_api_format()
    {
        $this->supportsNormalization(new \stdClass(), 'internal_api')->shouldReturn(false);
        $this->supportsNormalization(new PublishedProduct(), 'internal_api')->shouldReturn(false);
        $this->supportsNormalization(new Product(), 'any_other_format')->shouldReturn(false);
        $this->supportsNormalization(new Product(), 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_an_owned_product(
        NormalizerInterface $normalizer,
        PublishedProductManager $publishedManager,
        CategoryAccessRepository $categoryAccessRepo,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductRepositoryInterface $productRepository,
        NormalizerInterface $chainedNormalizer
    ) {
        $product = new Product();
        $uuid = $product->getUuid();
        $productRepository->find($uuid)->willReturn($product);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);

        $publishedProduct = new PublishedProduct();
        $version = new Version(PublishedProduct::class, 25, null, 'julia');
        $publishedProduct->setVersion($version);
        $publishedManager->findPublishedProductByOriginal($product)->willReturn($publishedProduct);
        $chainedNormalizer->normalize($version, 'internal_api', [])
            ->willReturn(['normalized_published_product_version']);

        $ownerGroups = [
            [
                'id' => 25,
                'name' => 'Catalog Manager',
            ],
        ];
        $categoryAccessRepo->getGrantedUserGroupsForEntityWithValues($product, Attributes::OWN_PRODUCTS, true)
                           ->willReturn($ownerGroups);
        $chainedNormalizer->normalize($ownerGroups, 'internal_api', [])->willReturn($ownerGroups);

        $normalizer->normalize($product, 'standard', [])->willReturn(['normalized_working_copy']);
        $normalizer->normalize($product, 'internal_api', [])->willReturn(
            [
                'id' => 42,
                'properties' => ['normalized_properties'],
                'meta' => [
                    'completeness' => ['normalized_completenesses'],
                    'required_missing_attributes' => ['normalized_missing_required_attributes'],
                ],
            ]
        );

        $this->normalize($product, 'internal_api')->shouldReturn(
            [
                'id' => 42,
                'properties' => ['normalized_properties'],
                'meta' => [
                    'completeness' => ['normalized_completenesses'],
                    'required_missing_attributes' => ['normalized_missing_required_attributes'],
                    'published' => ['normalized_published_product_version'],
                    'owner_groups' => $ownerGroups,
                    'is_owner' => true,
                    'working_copy' => ['normalized_working_copy'],
                    'draft_status' => null,
                ],
            ]
        );
    }

    function it_normalizes_a_product_with_an_ongoing_draft_and_filters_missing_required_attributes(
        NormalizerInterface $normalizer,
        PublishedProductManager $publishedManager,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        DraftApplierInterface $draftApplier,
        CategoryAccessRepository $categoryAccessRepo,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductRepositoryInterface $productRepository,
        MissingRequiredAttributesCalculatorInterface $missingRequiredAttributesCalculator,
        MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer,
        NormalizerInterface $chainedNormalizer,
        TokenInterface $token,
        EntityWithValuesDraftInterface $productDraft
    ) {
        $product = new Product();
        $uuid = $product->getUuid();
        $productRepository->find($uuid)->willReturn($product);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);

        $productDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $token->getUserIdentifier()->willReturn('mary');
        $draftRepository->findUserEntityWithValuesDraft($product, 'mary')->willReturn($productDraft);
        $draftApplier->applyAllChanges($product, $productDraft)->shouldBeCalled();

        $publishedManager->findPublishedProductByOriginal($product)->willReturn(null);

        $ownerGroups = [
            [
                'id' => 25,
                'name' => 'Catalog Manager',
            ],
        ];
        $categoryAccessRepo->getGrantedUserGroupsForEntityWithValues($product, Attributes::OWN_PRODUCTS, true)
                           ->willReturn($ownerGroups);
        $chainedNormalizer->normalize($ownerGroups, 'internal_api', [])->willReturn($ownerGroups);

        $normalizer->normalize($product, 'standard', [])->willReturn(['normalized_working_copy']);
        $normalizer->normalize($product, 'internal_api', [])->willReturn(
            [
                'id' => 42,
                'properties' => ['normalized_properties'],
                'meta' => [
                    'completeness' => ['normalized_completeness'],
                    'required_missing_attributes' => ['normalized_missing_required_attributes_for_original_product'],
                ],
            ]
        );

        $productWithDraftCompletenesses = new ProductCompletenessWithMissingAttributeCodesCollection(
            42,
            [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, ['name', 'description']),
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'fr_FR', 5, ['name']),
            ]
        );
        $missingRequiredAttributesCalculator->fromEntityWithFamily($product)->willReturn($productWithDraftCompletenesses);
        $missingRequiredAttributesNormalizer->normalize($productWithDraftCompletenesses)
                                            ->willReturn(['normalized_missing_required_attributes_for_draft']);

        $this->normalize($product, 'internal_api')->shouldReturn(
            [
                'id' => 42,
                'properties' => ['normalized_properties'],
                'meta' => [
                    'completeness' => ['normalized_completeness'],
                    'required_missing_attributes' => ['normalized_missing_required_attributes_for_draft'],
                    'published' => null,
                    'owner_groups' => $ownerGroups,
                    'is_owner' => false,
                    'working_copy' => ['normalized_working_copy'],
                    'draft_status' => EntityWithValuesDraftInterface::READY,
                ],
            ]
        );
    }

    function it_normalizes_a_viewable_only_product(
        NormalizerInterface $normalizer,
        PublishedProductManager $publishedManager,
        CategoryAccessRepository $categoryAccessRepo,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductRepositoryInterface $productRepository,
        NormalizerInterface $chainedNormalizer
    ) {
        $product = new Product();
        $uuid = $product->getUuid();
        $productRepository->find($uuid)->willReturn($product);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(false);

        $publishedManager->findPublishedProductByOriginal($product)->willReturn(null);

        $ownerGroups = [
            [
                'id' => 25,
                'name' => 'Catalog Manager',
            ],
        ];
        $categoryAccessRepo->getGrantedUserGroupsForEntityWithValues($product, Attributes::OWN_PRODUCTS, true)
                           ->willReturn($ownerGroups);
        $chainedNormalizer->normalize($ownerGroups, 'internal_api', [])->willReturn($ownerGroups);

        $normalizer->normalize($product, 'standard', [])->willReturn(['normalized_working_copy']);
        $normalizer->normalize($product, 'internal_api', [])->willReturn(
            [
                'id' => 42,
                'properties' => ['normalized_properties'],
                'meta' => [
                    'completeness' => ['normalized_completeness'],
                    'required_missing_attributes' => ['normalized_missing_required_attributes_for_original_product'],
                ],
            ]
        );

        $this->normalize($product, 'internal_api')->shouldReturn(
            [
                'id' => 42,
                'properties' => ['normalized_properties'],
                'meta' => [
                    'completeness' => ['normalized_completeness'],
                    'required_missing_attributes' => [],
                    'published' => null,
                    'owner_groups' => $ownerGroups,
                    'is_owner' => false,
                    'working_copy' => ['normalized_working_copy'],
                    'draft_status' => null,
                ],
            ]
        );
    }
}
