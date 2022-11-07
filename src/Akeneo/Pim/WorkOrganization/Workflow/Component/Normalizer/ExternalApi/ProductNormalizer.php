<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * Add metadata to product normalized data concerning only the PIM Enterprise Edition
 *
 * @author Laurent Petard <laurent.petard@akeneo.com>
 */
class ProductNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    const WORKFLOW_STATUS_WORKING_COPY = 'working_copy';
    const WORKFLOW_STATUS_READ_ONLY = 'read_only';
    const WORKFLOW_STATUS_IN_PROGRESS = 'draft_in_progress';
    const WORKFLOW_STATUS_WAITING_FOR_APPROVAL = 'proposal_waiting_for_approval';

    /** @var NormalizerInterface */
    protected $productNormalizer;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productDraftRepository;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param NormalizerInterface             $productNormalizer
     * @param EntityWithValuesDraftRepositoryInterface $productDraftRepository
     * @param AuthorizationCheckerInterface   $authorizationChecker
     * @param TokenStorageInterface           $tokenStorage
     */
    public function __construct(
        NormalizerInterface $productNormalizer,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->productNormalizer = $productNormalizer;
        $this->productDraftRepository = $productDraftRepository;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $normalizedProduct = $this->productNormalizer->normalize($product, $format, $context);
        $normalizedProduct['metadata']['workflow_status'] = $this->determineWorkflowStatus($product);

        return $normalizedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $this->productNormalizer->supportsNormalization($data, $format);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->productNormalizer instanceof CacheableSupportsMethodInterface
            && $this->productNormalizer->hasCacheableSupportsMethod();
    }

    /**
     * Determine the workflow status of a product according to the user permissions.
     *
     * @param ProductInterface $product
     *
     * @throws \LogicException If the user has not even the "view" permission on the product.
     *
     * @return string|null
     */
    protected function determineWorkflowStatus(ProductInterface $product)
    {
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $product);

        if ($isOwner) {
            return static::WORKFLOW_STATUS_WORKING_COPY;
        }

        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $product);

        if ($canEdit) {
            $userName = $this->tokenStorage->getToken()->getUserIdentifier();
            $productDraft = $this->productDraftRepository->findUserEntityWithValuesDraft($product, $userName);

            if (null === $productDraft) {
                return static::WORKFLOW_STATUS_WORKING_COPY;
            }

            Assert::implementsInterface($productDraft, EntityWithValuesDraftInterface::class);
            if (EntityWithValuesDraftInterface::READY === $productDraft->getStatus()) {
                return static::WORKFLOW_STATUS_WAITING_FOR_APPROVAL;
            }

            return static::WORKFLOW_STATUS_IN_PROGRESS;
        }

        $canView = $this->authorizationChecker->isGranted(Attributes::VIEW, $product);

        if ($canView) {
            return static::WORKFLOW_STATUS_READ_ONLY;
        }

        throw new \LogicException('A product should not be normalized if the user has not the "view" permission on it.');
    }
}
