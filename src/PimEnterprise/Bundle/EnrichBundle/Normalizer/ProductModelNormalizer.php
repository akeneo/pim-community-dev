<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Workflow\Applier\DraftApplierInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Product model normalizer
 */
class ProductModelNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var NormalizerInterface */
    private $normalizer;
    /** @var ProductDraftRepositoryInterface */
    private $draftRepository;
    /** @var DraftApplierInterface */
    private $draftApplier;
    /** @var CategoryAccessRepository */
    private $categoryAccessRepo;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;
    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /**
     * @param NormalizerInterface             $normalizer
     * @param ProductDraftRepositoryInterface $draftRepository
     * @param DraftApplierInterface           $draftApplier
     * @param CategoryAccessRepository        $categoryAccessRepo
     * @param TokenStorageInterface           $tokenStorage
     * @param AuthorizationCheckerInterface   $authorizationChecker
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ProductDraftRepositoryInterface $draftRepository,
        DraftApplierInterface $draftApplier,
        CategoryAccessRepository $categoryAccessRepo,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $productModelRepository
    ) {
        $this->normalizer = $normalizer;
        $this->draftRepository = $draftRepository;
        $this->draftApplier = $draftApplier;
        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->productModelRepository = $productModelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($productModel, $format = null, array $context = [])
    {
        $workingCopy = $this->productModelRepository->findOneByIdentifier($productModel->getCode());
        $normalizedWorkingCopy = $this->normalizer->normalize($workingCopy, 'standard', $context);
        $draftStatus = null;

        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $productModel);
        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $productModel);

        if (!$isOwner && $canEdit) {
            $username = $this->tokenStorage->getToken()->getUsername();
            $draft = $this->draftRepository->findUserProductModelDraft($productModel, $username);
            if (null !== $draft) {
                $draftStatus = $draft->getStatus();
                $this->draftApplier->applyAllChanges($productModel, $draft);
            }
        }

        $normalizedProductModel = $this->normalizer->normalize($productModel, 'internal_api', $context);

//        $ownerGroups = $this->categoryAccessRepo->getGrantedUserGroupsForProduct($productModel, Attributes::OWN_PRODUCTS);

        $normalizedProductModel['meta'] = array_merge(
            $normalizedProductModel['meta'],
            [
//                'owner_groups' => $this->serializer->normalize($ownerGroups, 'internal_api', $context),
                'is_owner'     => $this->authorizationChecker->isGranted(Attributes::OWN, $productModel),
                'working_copy' => $normalizedWorkingCopy,
                'draft_status' => $draftStatus
            ]
        );

        return $normalizedProductModel;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
