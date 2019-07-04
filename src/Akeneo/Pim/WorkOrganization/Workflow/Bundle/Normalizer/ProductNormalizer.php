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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
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
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Product normalizer
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var PublishedProductManager */
    protected $publishedManager;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $draftRepository;

    /** @var DraftApplierInterface */
    protected $draftApplier;

    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param NormalizerInterface                      $normalizer
     * @param PublishedProductManager                  $publishedManager
     * @param EntityWithValuesDraftRepositoryInterface $draftRepository
     * @param DraftApplierInterface                    $draftApplier
     * @param CategoryAccessRepository                 $categoryAccessRepo
     * @param TokenStorageInterface                    $tokenStorage
     * @param AuthorizationCheckerInterface            $authorizationChecker
     * @param ProductRepositoryInterface               $productRepository
     */
    public function __construct(
        NormalizerInterface $normalizer,
        PublishedProductManager $publishedManager,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        DraftApplierInterface $draftApplier,
        CategoryAccessRepository $categoryAccessRepo,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductRepositoryInterface $productRepository
    ) {
        $this->normalizer = $normalizer;
        $this->publishedManager = $publishedManager;
        $this->draftRepository = $draftRepository;
        $this->draftApplier = $draftApplier;
        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $normalizedWorkingCopy = $this->normalizer->normalize($product, 'standard', $context);
        $draftStatus = null;

        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $product);
        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $product);

        if (!$isOwner && $canEdit && null !== $draft = $this->findDraftForProduct($product)) {
            $draftStatus = $draft->getStatus();
            $this->draftApplier->applyAllChanges($product, $draft);
        }

        $normalizedProduct = $this->normalizer->normalize($product, 'internal_api', $context);

        $published = $this->publishedManager->findPublishedProductByOriginalId($product->getId());
        $ownerGroups = $this->categoryAccessRepo->getGrantedUserGroupsForEntityWithValues(
            $product,
            Attributes::OWN_PRODUCTS
        );

        $normalizedProduct['meta'] = array_merge(
            $normalizedProduct['meta'],
            [
                'published'    => $published ?
                    $this->serializer->normalize($published->getVersion(), 'internal_api', $context) :
                    null,
                'owner_groups' => $this->serializer->normalize($ownerGroups, 'internal_api', $context),
                'is_owner'     => $this->authorizationChecker->isGranted(Attributes::OWN, $product),
                'working_copy' => $normalizedWorkingCopy,
                'draft_status' => $draftStatus
            ]
        );

        return $normalizedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && !$data instanceof PublishedProductInterface && $format === 'internal_api';
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
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
        return $this->tokenStorage->getToken()->getUsername();
    }
}
