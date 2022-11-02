<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplierInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Product model normalizer
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductModelNormalizer implements NormalizerInterface, SerializerAwareInterface, CacheableSupportsMethodInterface
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var EntityWithValuesDraftRepositoryInterface */
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

    public function __construct(
        NormalizerInterface $normalizer,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
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
            $username = $this->tokenStorage->getToken()->getUserIdentifier();
            $draft = $this->draftRepository->findUserEntityWithValuesDraft($productModel, $username);
            if ($draft instanceof EntityWithValuesDraftInterface) {
                $draftStatus = $draft->getStatus();
                $this->draftApplier->applyAllChanges($productModel, $draft);
            }
        }

        $normalizedProductModel = $this->normalizer->normalize($productModel, 'internal_api', $context);

        $meta = [
            'is_owner' => $isOwner,
            'working_copy' => $normalizedWorkingCopy,
            'draft_status' => $draftStatus,
        ];
        if (!$isOwner && !$canEdit) {
            $meta['required_missing_attributes'] = [];
        }

        $normalizedProductModel['meta'] = array_merge($normalizedProductModel['meta'], $meta);

        return $normalizedProductModel;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->normalizer instanceof CacheableSupportsMethodInterface
            && $this->normalizer->hasCacheableSupportsMethod();
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
