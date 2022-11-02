<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MissingRequiredAttributesCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\MissingRequiredAttributesNormalizerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplierInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product normalizer
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductNormalizer implements NormalizerInterface, NormalizerAwareInterface, CacheableSupportsMethodInterface
{
    protected NormalizerInterface $normalizer;
    protected PublishedProductManager $publishedManager;
    protected EntityWithValuesDraftRepositoryInterface $draftRepository;
    protected DraftApplierInterface $draftApplier;
    protected CategoryAccessRepository $categoryAccessRepo;
    protected TokenStorageInterface $tokenStorage;
    protected AuthorizationCheckerInterface $authorizationChecker;
    protected NormalizerInterface $chainedNormalizer;
    protected ProductRepositoryInterface $productRepository;
    protected MissingRequiredAttributesCalculatorInterface $missingRequiredAttributesCalculator;
    protected MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer;

    public function __construct(
        NormalizerInterface $normalizer,
        PublishedProductManager $publishedManager,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        DraftApplierInterface $draftApplier,
        CategoryAccessRepository $categoryAccessRepo,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductRepositoryInterface $productRepository,
        MissingRequiredAttributesCalculatorInterface $missingRequiredAttributesCalculator,
        MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer
    ) {
        $this->normalizer = $normalizer;
        $this->publishedManager = $publishedManager;
        $this->draftRepository = $draftRepository;
        $this->draftApplier = $draftApplier;
        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->productRepository = $productRepository;
        $this->missingRequiredAttributesCalculator = $missingRequiredAttributesCalculator;
        $this->missingRequiredAttributesNormalizer = $missingRequiredAttributesNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $workingCopy = $this->productRepository->find($product->getUuid());
        $normalizedWorkingCopy = $this->normalizer->normalize($workingCopy, 'standard', $context);
        $draftStatus = null;

        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $product);
        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $product);

        if (!$isOwner && $canEdit && null !== $draft = $this->findDraftForProduct($product)) {
            $draftStatus = $draft->getStatus();
            $this->draftApplier->applyAllChanges($product, $draft);
        }

        $normalizedProduct = $this->normalizer->normalize($product, 'internal_api', $context);

        $published = $this->publishedManager->findPublishedProductByOriginal($product);
        $ownerGroups = $this->categoryAccessRepo->getGrantedUserGroupsForEntityWithValues(
            $product,
            Attributes::OWN_PRODUCTS,
            true
        );

        $meta = [
            'published' => $published ?
                $this->chainedNormalizer->normalize($published->getVersion(), 'internal_api', $context) :
                null,
            'owner_groups' => $this->chainedNormalizer->normalize($ownerGroups, 'internal_api', $context),
            'is_owner' => $isOwner,
            'working_copy' => $normalizedWorkingCopy,
            'draft_status' => $draftStatus
        ];

        // if a draft is ongoing, we have to recompute the missing required attributes based on the draft values
        if (null !== $draftStatus) {
            $completenesses = $this->missingRequiredAttributesCalculator->fromEntityWithFamily($product);
            $meta['required_missing_attributes'] = $this->missingRequiredAttributesNormalizer->normalize($completenesses);
        } elseif (!$isOwner && !$canEdit) {
            $meta['required_missing_attributes'] = [];
        }

        $normalizedProduct['meta'] = array_merge($normalizedProduct['meta'], $meta);

        return $normalizedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductInterface && !$data instanceof PublishedProductInterface && $format === 'internal_api';
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setNormalizer(NormalizerInterface $normalizer)
    {
        $this->chainedNormalizer = $normalizer;
    }

    /**
     * Find a product draft for the specified product
     *
     * @param ProductInterface $product
     *
     * @return EntityWithValuesDraftInterface|null
     */
    protected function findDraftForProduct(ProductInterface $product)
    {
        return $this->draftRepository->findUserEntityWithValuesDraft($product, $this->getUsername());
    }

    /**
     * Return the current username
     *
     * @return string
     */
    protected function getUsername()
    {
        return $this->tokenStorage->getToken()->getUserIdentifier();
    }

    /**
     * Filters the 'missing required attributes' based on the user's permissions
     */
    private function filterMissingRequiredAttributes(array $requiredMissingAttributes): array
    {
        $filteredRequiredMissingAttributes = [];

        foreach ($requiredMissingAttributes as $index => $missingForChannel) {
            $filteredRequiredMissingAttributes[$index] = [
                'channel' => $missingForChannel['channel'],
            ];

            foreach ($missingForChannel['locales'] as $localeCode => $missingForLocale) {
                $filteredRequiredMissingAttributes[$index]['locales'][$localeCode] = [
                    'missing' => $missingForLocale['missing'],
                ];
            }
        }

        return $filteredRequiredMissingAttributes;
    }
}
